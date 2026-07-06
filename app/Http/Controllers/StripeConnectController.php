<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\OAuth;
use Stripe\Stripe;

/**
 * Stripe Connect OAuth onboarding.
 *
 * Flow:
 *   1. GET /stripe/connect           → redirect user to Stripe's OAuth URL
 *   2. GET /stripe/connect/callback  → Stripe redirects here with ?code= (or ?error=)
 *                                      Exchange code → connected account id → save on User
 *   3. POST /stripe/disconnect       → clear the user's stripe_account_id
 *
 * Env vars (in .env; never commit):
 *   STRIPE_SECRET              — platform secret key (sk_test_… or sk_live_…)
 *   STRIPE_CONNECT_CLIENT_ID   — "ca_…" client ID from Stripe → Connect → Settings
 */
class StripeConnectController extends Controller
{
    public function connect(Request $request)
    {
        $clientId = (string) env('STRIPE_CONNECT_CLIENT_ID', '');
        if ($clientId === '') {
            return redirect()->route('showProfile')
                ->with('error', 'Stripe Connect is not configured on this platform yet.');
        }

        // CSRF for the OAuth round-trip: a random state string stored in
        // session and compared on callback.
        $state = Str::random(40);
        $request->session()->put('stripe_oauth_state', $state);

        $params = [
            'response_type' => 'code',
            'client_id'     => $clientId,
            'scope'         => 'read_write',
            'redirect_uri'  => route('stripe.connect.callback'),
            'state'         => $state,
            // Pre-fill the user's email on Stripe's onboarding form.
            'stripe_user[email]' => Auth::user()->email ?? '',
        ];

        return redirect('https://connect.stripe.com/oauth/authorize?' . http_build_query($params));
    }

    public function callback(Request $request)
    {
        // Verify state
        $expectedState = $request->session()->pull('stripe_oauth_state');
        $providedState = (string) $request->query('state', '');
        if ($expectedState === null || !hash_equals((string) $expectedState, $providedState)) {
            return $this->popupClose('error', 'Stripe connection could not be verified. Please try again.');
        }

        // User denied or Stripe returned an error
        if ($request->query('error')) {
            Log::info('Stripe Connect denied/errored', [
                'user_id'           => Auth::id(),
                'error'             => $request->query('error'),
                'error_description' => $request->query('error_description'),
            ]);
            return $this->popupClose('error', 'Stripe authorization was cancelled or failed.');
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return $this->popupClose('error', 'Stripe did not return an authorization code.');
        }

        $secret = (string) env('STRIPE_SECRET', '');
        if ($secret === '') {
            return $this->popupClose('error', 'Stripe Connect is not configured on this platform yet.');
        }

        Stripe::setApiKey($secret);

        try {
            $response = OAuth::token([
                'grant_type' => 'authorization_code',
                'code'       => $code,
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe Connect token exchange failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);
            return $this->popupClose('error', 'Could not complete Stripe connection. Please try again.');
        }

        $connectedId = (string) ($response->stripe_user_id ?? '');
        if ($connectedId === '' || !str_starts_with($connectedId, 'acct_')) {
            Log::error('Stripe Connect response missing stripe_user_id', [
                'user_id' => Auth::id(),
            ]);
            return $this->popupClose('error', 'Stripe response was unexpected. Please try again.');
        }

        $user = User::find(Auth::id());
        $user->stripe_account_id = $connectedId;
        $user->save();

        return $this->popupClose('success', 'Stripe account connected.');
    }

    /**
     * Tiny page shown in the OAuth popup after the callback. It self-
     * closes (a script-opened window can close itself even when COOP
     * severed the opener reference from the Stripe round-trip); if it
     * somehow isn't a popup, it navigates to the block editor instead.
     * The block form polls /stripe/status independently, so the
     * "connected" state reflects even if the close/redirect is blocked.
     */
    private function popupClose(string $status, string $message)
    {
        return response()->view('stripe.popup-close', [
            'status'  => $status,
            'message' => $message,
        ]);
    }

    /**
     * Lightweight JSON status the block editor polls after opening the
     * Connect popup, so the connected state reflects without relying on
     * cross-window messaging.
     */
    public function status(Request $request)
    {
        $user = User::find(Auth::id());
        return response()->json([
            'connected'  => !empty($user->stripe_account_id),
            'account_id' => $user->stripe_account_id ?: null,
        ]);
    }

    public function disconnect(Request $request)
    {
        $user = User::find(Auth::id());
        if ($user && $user->stripe_account_id) {
            $user->stripe_account_id = null;
            $user->save();
        }

        // The block editor calls this via fetch — answer with JSON so it
        // can update in place. Non-AJAX callers still get a redirect.
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['connected' => false]);
        }
        return redirect()->route('showProfile')
            ->with('success', 'Stripe account disconnected.');
    }
}
