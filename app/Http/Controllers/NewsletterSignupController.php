<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Handles public submissions from the "newsletter_signup" block.
 *
 * Uses Mailchimp Marketing API v3.0 via PHP's built-in curl. No SDK.
 *
 * API key + list ID live in the Link row's type_params JSON column;
 * they are NEVER rendered into HTML (see blocks/newsletter_signup/display.blade.php).
 *
 * Outcome → session flash key → banner:
 *   subscribed (new)        → newsletter_success
 *   subscribed (existing)   → newsletter_already
 *   pending                 → newsletter_pending
 *   unsubscribed            → newsletter_previously_unsubscribed
 *   cleaned / compliance    → newsletter_error (friendly; details server-logged)
 *   HTTP / curl / config    → newsletter_error (friendly; details server-logged)
 *   honeypot filled         → newsletter_success (silent bot tarpit)
 */
class NewsletterSignupController extends Controller
{
    public function subscribe(Request $request, $id)
    {
        $link = Link::find($id);
        if (!$link || $link->type !== 'newsletter_signup') {
            abort(404);
        }

        // Honeypot: pretend success but skip the API call entirely.
        if (filled($request->input('website'))) {
            return back()
                ->with('newsletter_success', (int) $id)
                ->withFragment("newsletter-signup-$id");
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => ['required', 'email:rfc', 'max:255'],
        ]);

        // Read credentials from server-side-only JSON blob.
        $params = json_decode($link->type_params ?? '{}', true);
        if (!is_array($params)) {
            $params = [];
        }
        $apiKey = (string) ($params['api_key'] ?? '');
        $listId = (string) ($params['list_id'] ?? '');

        if ($apiKey === '' || $listId === '') {
            Log::error('Newsletter block misconfigured', ['link_id' => (int) $id]);
            return $this->fail($id, $request);
        }

        // Data center is the suffix after the final dash in the API key
        // (e.g. "abc123def456-us1" → "us1").
        $dashPos = strrpos($apiKey, '-');
        if ($dashPos === false || $dashPos === strlen($apiKey) - 1) {
            Log::error('Newsletter block: malformed API key', ['link_id' => (int) $id]);
            return $this->fail($id, $request);
        }
        $dc = substr($apiKey, $dashPos + 1);
        if (!preg_match('/^[A-Za-z0-9]+$/', $dc)) {
            Log::error('Newsletter block: invalid data center suffix', ['link_id' => (int) $id]);
            return $this->fail($id, $request);
        }

        $email = strtolower(trim($data['email']));
        // Mailchimp identifies members by the MD5 hash of the lowercased email.
        $subscriberHash = md5($email);
        $url = "https://{$dc}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}";

        // status_if_new respects prior unsubscribe state rather than forcing
        // them back to subscribed — required for compliance.
        $body = [
            'email_address' => $email,
            'status_if_new' => 'subscribed',
            'merge_fields'  => [
                'FNAME' => $data['first_name'],
                'LNAME' => $data['last_name'],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_USERPWD        => 'anystring:' . $apiKey,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $rawResponse = curl_exec($ch);
        $httpCode    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false || $curlError !== '') {
            Log::error('Newsletter Mailchimp curl failed', [
                'link_id' => (int) $id,
                'error'   => $curlError,
            ]);
            return $this->fail($id, $request);
        }

        $response = json_decode($rawResponse, true);
        if (!is_array($response)) {
            Log::error('Newsletter Mailchimp non-JSON response', [
                'link_id'   => (int) $id,
                'http_code' => $httpCode,
            ]);
            return $this->fail($id, $request);
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $status       = (string) ($response['status'] ?? '');
            $timestampOpt = (string) ($response['timestamp_opt'] ?? '');

            // Detect new-vs-existing by whether the opt-in timestamp is fresh.
            $optTime = strtotime($timestampOpt);
            $isFresh = $optTime !== false && $optTime > (time() - 60);

            if ($status === 'subscribed') {
                return back()
                    ->with($isFresh ? 'newsletter_success' : 'newsletter_already', (int) $id)
                    ->withFragment("newsletter-signup-$id");
            }
            if ($status === 'pending') {
                return back()
                    ->with('newsletter_pending', (int) $id)
                    ->withFragment("newsletter-signup-$id");
            }
            if ($status === 'unsubscribed') {
                return back()
                    ->with('newsletter_previously_unsubscribed', (int) $id)
                    ->withFragment("newsletter-signup-$id");
            }
            if ($status === 'cleaned' || $status === 'archived') {
                Log::info('Newsletter non-actionable status', [
                    'link_id' => (int) $id,
                    'status'  => $status,
                ]);
                return $this->fail($id, $request);
            }
            // Unexpected 2xx + unknown status — treat as success so visitor
            // isn't blocked, but log for investigation.
            Log::warning('Newsletter unknown member status', [
                'link_id' => (int) $id,
                'status'  => $status,
            ]);
            return back()
                ->with('newsletter_success', (int) $id)
                ->withFragment("newsletter-signup-$id");
        }

        // HTTP error: server-log the full Mailchimp error detail; visitor sees
        // a generic friendly message with no credentials or internals leaked.
        Log::error('Newsletter Mailchimp error response', [
            'link_id'   => (int) $id,
            'http_code' => $httpCode,
            'mc_title'  => $response['title']  ?? null,
            'mc_detail' => $response['detail'] ?? null,
        ]);
        return $this->fail($id, $request);
    }

    private function fail($id, Request $request)
    {
        return back()
            ->with('newsletter_error', (int) $id)
            ->withFragment("newsletter-signup-$id")
            ->withInput($request->except(['website']));
    }
}
