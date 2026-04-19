<?php
/*
 * Mail Minted admin API for LinkStack.
 *
 * Provisioning in Mail Minted's backend (see
 * backend/src/services/linkstack/client.js) calls these endpoints to
 * create / enable / disable / delete a LinkStack user per customer
 * domain. All endpoints require the `MAILMINTED_ADMIN_API_TOKEN` env
 * var as a bearer token, verified inside each handler via
 * mailminted_admin_check() — Laravel's Route::middleware() only takes
 * string middleware names, not closures, so we can't stack it as
 * middleware without registering a proper class.
 *
 * Endpoints:
 *   POST   /api/admin/users
 *   PATCH  /api/admin/users/{id}
 *   DELETE /api/admin/users/{id}
 */

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// ---------------------------------------------------------------------
// Returns null when the bearer token matches. Returns a JsonResponse
// when it doesn't (the handler should return that response directly).
// ---------------------------------------------------------------------
if (!function_exists('mailminted_admin_check')) {
    function mailminted_admin_check(Request $request) {
        $expected = env('MAILMINTED_ADMIN_API_TOKEN');
        if (!$expected) {
            return response()->json(['error' => 'admin API not configured'], 503);
        }
        $header = $request->header('Authorization', '');
        $presented = Str::startsWith($header, 'Bearer ')
            ? substr($header, 7)
            : null;
        if (!$presented || !hash_equals($expected, $presented)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }
        return null;
    }
}

Route::prefix('admin')->group(function () {

    Route::post('/users', function (Request $request) {
        if ($deny = mailminted_admin_check($request)) return $deny;

        $email = trim((string) $request->input('email', ''));
        $username = trim((string) $request->input('username', ''));
        $customDomain = strtolower(trim((string) $request->input('custom_domain', '')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'valid email required'], 400);
        }
        if (!preg_match('/^[a-z0-9][a-z0-9-]{1,62}$/i', $username)) {
            return response()->json(['error' => 'username must be alphanumeric + hyphens, 2-63 chars'], 400);
        }
        if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $customDomain)) {
            return response()->json(['error' => 'invalid custom_domain'], 400);
        }

        // Reuse existing row on conflict so provisioning is idempotent
        // (a retried Mail Minted provision shouldn't error).
        $existing = User::where('email', $email)
            ->orWhere('custom_domain', $customDomain)
            ->orWhere('littlelink_name', $username)
            ->first();
        if ($existing) {
            return response()->json([
                'user_id' => $existing->id,
                'username' => $existing->littlelink_name,
                'email' => $existing->email,
                'custom_domain' => $existing->custom_domain,
                'reused' => true,
            ]);
        }

        // The customer never uses this password; they SSO in via JWT.
        // Still set something strong in case SSO is ever disabled.
        $user = User::create([
            'name' => $username,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(40)),
            'littlelink_name' => $username,
            'littlelink_description' => '',
            'custom_domain' => $customDomain,
            'block' => 'no',
        ]);

        return response()->json([
            'user_id' => $user->id,
            'username' => $user->littlelink_name,
            'email' => $user->email,
            'custom_domain' => $user->custom_domain,
        ], 201);
    });

    Route::patch('/users/{id}', function (Request $request, $id) {
        if ($deny = mailminted_admin_check($request)) return $deny;

        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'user not found'], 404);
        }
        $state = $request->input('state');
        if (!in_array($state, ['enabled', 'disabled'], true)) {
            return response()->json(['error' => 'state must be enabled or disabled'], 400);
        }
        $user->block = $state === 'disabled' ? 'yes' : 'no';
        $user->save();
        return response()->json([
            'user_id' => $user->id,
            'state' => $state,
            'block' => $user->block,
        ]);
    });

    Route::delete('/users/{id}', function (Request $request, $id) {
        if ($deny = mailminted_admin_check($request)) return $deny;

        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'user not found'], 404);
        }
        // Don't allow deleting user_id=1 (installer admin) by accident.
        if ((int) $id === 1) {
            return response()->json(['error' => 'refusing to delete admin user_id=1'], 409);
        }
        $user->delete();
        return response()->json(['user_id' => (int) $id, 'deleted' => true]);
    });

});
