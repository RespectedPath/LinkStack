<?php
/*
 * Mail Minted → LinkStack SSO bridge.
 *
 * Drop this file into the LinkStack Laravel app at:
 *   routes/sso-mailminted.php
 *
 * Then include it from routes/web.php with:
 *   require __DIR__ . '/sso-mailminted.php';
 *
 * Dependencies (composer require in the LinkStack container):
 *   firebase/php-jwt:^6.10
 *
 * Environment (add to LinkStack's .env):
 *   MAILMINTED_SSO_SHARED_SECRET=<same value as backend/.env LINKSTACK_SSO_SHARED_SECRET>
 *
 * How it works:
 *   1. Mail Minted mints a short-lived HS256 JWT (60-sec TTL) with the
 *      LinkStack user_id as `sub` and redirects the customer to
 *      /sso/mailminted?token=<JWT> on this LinkStack host.
 *   2. This route verifies the signature + issuer + audience + expiry.
 *   3. On success, it logs the matching user in via Auth::loginUsingId
 *      and redirects to /dashboard. On any failure, it redirects to
 *      /login with an error flash.
 *
 * Security notes:
 *   - Anti-replay via 60-sec TTL. A one-shot nonce table is overkill at
 *     this window and adds a DB round-trip per redirect. If a leaked
 *     token in the Referrer ever becomes a concern, add a jti/seen
 *     table here.
 *   - Use HTTPS end-to-end. The token rides in the querystring; a
 *     plaintext hop exposes it.
 *   - Rotate MAILMINTED_SSO_SHARED_SECRET by updating both sides and
 *     restarting. In-flight tokens (≤60 sec) are invalidated — that's
 *     the intended behavior.
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/sso/mailminted', function (Request $request) {
    $token = $request->query('token');
    $secret = env('MAILMINTED_SSO_SHARED_SECRET');

    if (!$token || !$secret) {
        return redirect('/login')->with('error', 'Invalid SSO link.');
    }

    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
    } catch (\Throwable $e) {
        Log::warning('Mail Minted SSO rejected: ' . $e->getMessage());
        return redirect('/login')->with('error', 'SSO token is invalid or expired.');
    }

    if (($decoded->iss ?? null) !== 'mailminted' || ($decoded->aud ?? null) !== 'linkstack') {
        return redirect('/login')->with('error', 'SSO token issuer mismatch.');
    }

    $userId = $decoded->sub ?? null;
    if (!$userId) {
        return redirect('/login')->with('error', 'SSO token missing subject.');
    }

    // Auth::loginUsingId returns false if the user does not exist.
    if (!Auth::loginUsingId($userId)) {
        Log::warning('Mail Minted SSO: no LinkStack user for id ' . $userId);
        return redirect('/login')->with('error', 'Account not found on this LinkStack instance.');
    }

    $request->session()->regenerate();
    return redirect('/dashboard');
})->name('mailminted.sso');


/*
 * SSO Logout — the mirror of the login handoff above.
 *
 * Both apps' logout buttons bounce through this endpoint so LinkStack
 * and Mail Minted's sessions clear together. The customer never ends
 * up authenticated on one side but not the other.
 *
 *   From LinkStack: sidebar logout POSTs / GETs here directly.
 *   From Mail Minted: frontend logout button redirects here with
 *     ?return=<mailminted-post-logout-url> so the browser bounces
 *     back to Mail Minted after LinkStack's session is cleared.
 *
 * Security:
 *   - No token required (users can always sign themselves out).
 *   - The return URL is validated against MAILMINTED_APP_URL to
 *     prevent open-redirect abuse. Anything not under that origin
 *     falls back to a safe default.
 *   - GET is accepted (browser redirects can't POST easily), and
 *     the operation is idempotent, so CSRF exemption is fine here.
 */
Route::get('/sso/logout', function (Request $request) {
    // Clear the Laravel session — mirror of what
    // AuthenticatedSessionController@destroy does.
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Validate ?return against the configured Mail Minted origin so a
    // malicious link can't turn this route into an open redirector.
    $return = $request->query('return');
    $mmUrl  = rtrim((string) env('MAILMINTED_APP_URL', ''), '/');

    if ($return && $mmUrl && str_starts_with($return, $mmUrl . '/')) {
        return redirect()->away($return);
    }

    // No return URL provided (or it didn't validate). Redirect to the
    // Mail Minted logout-complete page if we know where that lives,
    // otherwise the LinkStack login page as a last resort.
    if ($mmUrl) {
        return redirect()->away($mmUrl . '/logout-complete');
    }
    return redirect('/login');
})->name('mailminted.sso.logout');
