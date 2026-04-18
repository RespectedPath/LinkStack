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
