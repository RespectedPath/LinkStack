<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Creates a Stripe Checkout Session for the "stripe_payment" block.
 *
 * Flow:
 *   1. Visitor clicks a button on the block's display → POST /stripe/checkout/{link_id}
 *   2. This controller decides the amount from the block's stored config
 *      plus the POST body:
 *
 *        - fixed_price, single option → options[0]
 *        - fixed_price, multi-option  → options[option_index] (submit-button value)
 *        - tip_jar                    → visitor's amount input validated
 *                                       against min_amount_cents
 *
 *      For backward compatibility, if the block was saved before the
 *      multi-option expansion (no `options` array, only the legacy
 *      `amount_cents` field), we synthesize a single-option list from
 *      that legacy field. Pre-expansion blocks therefore keep working
 *      with no migration.
 *
 *   3. Creates a Session with application_fee_amount: 0 and
 *      transfer_data.destination = page owner's connected account —
 *      platform takes nothing. Currency-aware amount conversion lives
 *      in blocks/stripe_payment/currencies.php so write-side (handler)
 *      and read-side (here) agree.
 *   4. Redirect the visitor to session.url.
 *
 * Secrets (.env → never rendered in HTML):
 *   STRIPE_SECRET — platform secret key (sk_test_… or sk_live_…)
 *
 * Users table: users.stripe_account_id holds the connected account and
 * is never rendered in the block's display template.
 */
class StripePaymentController extends Controller
{
    public function checkout(Request $request, $id)
    {
        require_once base_path('blocks/stripe_payment/currencies.php');

        $link = Link::find($id);
        if (!$link || $link->type !== 'stripe_payment') {
            abort(404);
        }

        $owner = User::find($link->user_id);
        if (!$owner || empty($owner->stripe_account_id)) {
            Log::error('Stripe payment: page owner has no connected Stripe account', [
                'link_id' => (int) $id,
                'user_id' => $link->user_id,
            ]);
            return $this->fail($id);
        }

        $secret = (string) env('STRIPE_SECRET', '');
        if ($secret === '') {
            Log::error('Stripe payment: STRIPE_SECRET not configured');
            return $this->fail($id);
        }

        $params = json_decode($link->type_params ?? '{}', true);
        if (!is_array($params)) {
            $params = [];
        }

        $mode               = (string) ($params['mode'] ?? 'fixed_price');
        $currency           = strtolower((string) ($params['currency'] ?? ''));
        $productDescription = (string) ($params['product_description'] ?? '');
        $successUrl         = (string) ($params['success_url'] ?? '');
        $cancelUrl          = (string) ($params['cancel_url'] ?? '');

        if ($currency === '' || $productDescription === '' || $successUrl === '' || $cancelUrl === '') {
            Log::error('Stripe payment: block configuration incomplete', [
                'link_id' => (int) $id,
            ]);
            return $this->fail($id);
        }

        // --- Determine the amount to charge ---
        $amountSmallest = $this->resolveAmount($mode, $params, $request, $link, $id, $currency);
        if ($amountSmallest === null) {
            return $this->fail($id);
        }

        // Stripe caps at 99,999,999 in the smallest unit (USD $999,999.99).
        // Enforce server-side regardless of mode or client-side limits.
        if ($amountSmallest < 1 || $amountSmallest > 99999999) {
            Log::info('Stripe payment: amount out of allowed range', [
                'link_id' => (int) $id,
                'amount'  => $amountSmallest,
            ]);
            return $this->fail($id);
        }

        Stripe::setApiKey($secret);

        try {
            $session = Session::create([
                'mode' => 'payment',
                'line_items' => [[
                    'quantity'   => 1,
                    'price_data' => [
                        'currency'     => $currency,
                        'unit_amount'  => $amountSmallest,
                        'product_data' => [
                            'name' => $productDescription,
                        ],
                    ],
                ]],
                'payment_intent_data' => [
                    // Zero platform application fee — deliberate product
                    // decision. Stripe's own processing fee still applies.
                    'application_fee_amount' => 0,
                    'on_behalf_of' => $owner->stripe_account_id,
                    'transfer_data' => [
                        'destination' => $owner->stripe_account_id,
                    ],
                    'metadata' => [
                        'linkstack_link_id' => (string) $link->id,
                        'linkstack_user_id' => (string) $owner->id,
                        'linkstack_mode'    => $mode,
                    ],
                ],
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe payment: Checkout Session creation failed', [
                'link_id' => (int) $id,
                'error'   => $e->getMessage(),
            ]);
            return $this->fail($id);
        }

        if (empty($session->url)) {
            Log::error('Stripe payment: Checkout Session has no URL', [
                'link_id' => (int) $id,
            ]);
            return $this->fail($id);
        }

        return redirect()->away($session->url);
    }

    /**
     * Returns the amount to charge in Stripe's smallest unit, or null
     * if the request can't be fulfilled (misconfigured, below min, etc).
     * All validation is done server-side regardless of what the client
     * submitted.
     */
    private function resolveAmount(string $mode, array $params, Request $request, $link, $id, string $currency)
    {
        if ($mode === 'tip_jar') {
            // Visitor-entered amount in decimal form from the tip form.
            $raw = $request->input('amount');
            if ($raw === null || $raw === '') {
                return null;
            }
            if (!is_numeric($raw)) {
                return null;
            }
            $entered   = (float) $raw;
            $smallest  = stripe_payment_amount_to_smallest_unit($entered, $currency);
            $minSmallest = (int) ($params['min_amount_cents'] ?? 0);
            if ($smallest < $minSmallest) {
                Log::info('Stripe tip: amount below minimum', [
                    'link_id'     => (int) $id,
                    'entered'     => $entered,
                    'entered_su'  => $smallest,
                    'min_su'      => $minSmallest,
                ]);
                return null;
            }
            return $smallest;
        }

        // fixed_price (default)
        $options = $params['options'] ?? [];
        // Backward compat — pre-expansion blocks only have amount_cents.
        if (empty($options) && isset($params['amount_cents'])) {
            $options = [[
                'label'        => (string) ($link->link ?? 'Pay'),
                'amount_cents' => (int) $params['amount_cents'],
            ]];
        }
        if (empty($options)) {
            Log::error('Stripe payment: fixed_price block has no options configured', [
                'link_id' => (int) $id,
            ]);
            return null;
        }
        $optionIndex = (int) $request->input('option_index', 0);
        if (!array_key_exists($optionIndex, $options)) {
            $optionIndex = 0; // defensive — single-button form submits no option_index
        }
        $amt = (int) ($options[$optionIndex]['amount_cents'] ?? 0);
        return $amt > 0 ? $amt : null;
    }

    private function fail($id)
    {
        return back()
            ->with('stripe_payment_error', (int) $id)
            ->withFragment("stripe-payment-$id");
    }
}
