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
            return redirect()->route('showProfile')
                ->with('error', 'Stripe connection could not be verified. Please try again.');
        }

        // User denied or Stripe returned an error
        if ($request->query('error')) {
            Log::info('Stripe Connect denied/errored', [
                'user_id'           => Auth::id(),
                'error'             => $request->query('error'),
                'error_description' => $request->query('error_description'),
            ]);
            return redirect()->route('showProfile')
                ->with('error', 'Stripe authorization was cancelled or failed.');
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()->route('showProfile')
                ->with('error', 'Stripe did not return an authorization code.');
        }

        $secret = (string) env('STRIPE_SECRET', '');
        if ($secret === '') {
            return redirect()->route('showProfile')
                ->with('error', 'Stripe Connect is not configured on this platform yet.');
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
            return redirect()->route('showProfile')
                ->with('error', 'Could not complete Stripe connection. Please try again.');
        }

        $connectedId = (string) ($response->stripe_user_id ?? '');
        if ($connectedId === '' || !str_starts_with($connectedId, 'acct_')) {
            Log::error('Stripe Connect response missing stripe_user_id', [
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('showProfile')
                ->with('error', 'Stripe response was unexpected. Please try again.');
        }

        $user = User::find(Auth::id());
        $user->stripe_account_id = $connectedId;
        $user->save();

        return redirect()->route('showProfile')
            ->with('success', 'Stripe account connected.');
    }

    public function disconnect(Request $request)
    {
        $user = User::find(Auth::id());
        if ($user && $user->stripe_account_id) {
            $user->stripe_account_id = null;
            $user->save();
        }

        return redirect()->route('showProfile')
            ->with('success', 'Stripe account disconnected.');
    }
}
