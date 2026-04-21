<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

/**
 * Receives Stripe webhook events.
 *
 * Signature is verified against STRIPE_WEBHOOK_SECRET using Stripe's
 * official helper. Route is excluded from CSRF via
 * app/Http/Middleware/VerifyCsrfToken::$except.
 *
 * Currently handles payment_intent.succeeded (logged). Additional
 * events can be added under the switch without changing the shape
 * of the route or verification logic.
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = (string) env('STRIPE_WEBHOOK_SECRET', '');
        if ($secret === '') {
            // Without a configured secret we can't verify; fail closed.
            Log::warning('Stripe webhook hit but STRIPE_WEBHOOK_SECRET is unset');
            return response()->json(['error' => 'Webhook not configured'], 500);
        }

        $payload   = $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: signature verification failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $pi = $event->data->object;
                Log::info('Stripe payment_intent.succeeded', [
                    'event_id'           => $event->id,
                    'payment_intent_id'  => $pi->id ?? null,
                    'amount'             => $pi->amount ?? null,
                    'currency'           => $pi->currency ?? null,
                    'connected_account'  => $event->account ?? null,
                    'customer_email'     => $pi->receipt_email ?? null,
                    'metadata'           => $pi->metadata ?? null,
                ]);
                break;

            default:
                // Unhandled event types are not an error — we ACK them so
                // Stripe doesn't retry. Log at debug level only.
                Log::debug('Stripe webhook: unhandled event type', [
                    'event_id' => $event->id,
                    'type'     => $event->type,
                ]);
                break;
        }

        return response()->json(['received' => true]);
    }
}
