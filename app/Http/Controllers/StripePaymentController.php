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
 *   1. Visitor clicks the block's button → POST /stripe/checkout/{link_id}
 *   2. This controller loads the Link, finds the page owner, reads their
 *      connected stripe_account_id, builds a Session with transfer_data
 *      so the funds settle on the page owner's account.
 *   3. application_fee_amount = 0 — platform takes nothing (deliberate
 *      product decision; Stripe still deducts its own processing fee).
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
        $link = Link::find($id);
        if (!$link || $link->type !== 'stripe_payment') {
            abort(404);
        }

        // Look up the page owner to get their connected Stripe account.
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

        // Decode the block's configuration.
        $params = json_decode($link->type_params ?? '{}', true);
        if (!is_array($params)) {
            $params = [];
        }
        $amountCents         = (int) ($params['amount_cents'] ?? 0);
        $currency            = strtolower((string) ($params['currency'] ?? ''));
        $productDescription  = (string) ($params['product_description'] ?? '');
        $successUrl          = (string) ($params['success_url'] ?? '');
        $cancelUrl           = (string) ($params['cancel_url'] ?? '');

        if ($amountCents < 50 || $currency === '' || $productDescription === ''
            || $successUrl === '' || $cancelUrl === '') {
            Log::error('Stripe payment: block configuration incomplete', [
                'link_id' => (int) $id,
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
                        'unit_amount'  => $amountCents,
                        'product_data' => [
                            'name' => $productDescription,
                        ],
                    ],
                ]],
                'payment_intent_data' => [
                    // Zero platform application fee — deliberate product
                    // decision. Stripe's own processing fee still applies.
                    'application_fee_amount' => 0,
                    // Connected account whose business info appears on the
                    // receipt and who is legally the merchant of record.
                    'on_behalf_of' => $owner->stripe_account_id,
                    // Settle funds to the page owner's connected account.
                    'transfer_data' => [
                        'destination' => $owner->stripe_account_id,
                    ],
                    // Stripe metadata — handy for reconciliation in the
                    // webhook and in the Stripe dashboard.
                    'metadata' => [
                        'linkstack_link_id' => (string) $link->id,
                        'linkstack_user_id' => (string) $owner->id,
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

    private function fail($id)
    {
        return back()
            ->with('stripe_payment_error', (int) $id)
            ->withFragment("stripe-payment-$id");
    }
}
