<?php

require_once __DIR__ . '/currencies.php';

/**
 * Handles the logic for "stripe_payment" link type.
 *
 * Two modes, selected via the `mode` form field:
 *
 *   fixed_price  → 1..3 preset options. Each option has a label and an
 *                  amount. Single option is the original behaviour
 *                  (unchanged on the public page). Stored as an
 *                  `options` array inside type_params. A legacy
 *                  `amount_cents` top-level key mirrors options[0] for
 *                  forward readers that might not look at `options`.
 *
 *   tip_jar      → visitor enters their own amount on the public page
 *                  before checkout. Stored as `min_amount_cents` and
 *                  `suggested_amount_cents`. The button label lives in
 *                  the `link` column; the currency lives in type_params.
 *
 * Storage mapping (UserController::saveLink splits linkData between
 * real links columns and type_params JSON at
 * app/Http/Controllers/UserController.php:230-292):
 *
 *   Columns:
 *     - title → links.title       (public heading)
 *     - link  → links.link        (public CTA button label)
 *
 *   type_params JSON (public-safe; the Stripe secret + connected account
 *   id are never written here):
 *     - mode                   'fixed_price' | 'tip_jar'
 *     - currency               lowercase ISO code
 *     - product_description    shown on Stripe's checkout page
 *     - success_url / cancel_url
 *     - options (fixed_price)  array of { label, amount_cents }
 *     - amount_cents (fixed_price legacy mirror of options[0])
 *     - min_amount_cents (tip_jar)
 *     - suggested_amount_cents (tip_jar, nullable)
 *
 * All amounts are stored in Stripe's smallest unit for the selected
 * currency — hundredths for most currencies, whole units for
 * zero-decimal currencies like JPY. See currencies.php for the
 * canonical conversion.
 *
 * button_id is intentionally omitted; saveLink defaults it to 1.
 */
function handleLinkType($request, $linkType)
{
    $mode = (string) $request->input('mode', 'fixed_price');
    if (!in_array($mode, ['fixed_price', 'tip_jar'], true)) {
        $mode = 'fixed_price';
    }
    $currency = strtolower(trim((string) $request->input('currency', '')));

    // --- Validation rules ---
    $rules = [
        'title'               => ['required', 'string', 'max:100'],
        'link'                => ['required', 'string', 'max:50'],
        'mode'                => ['required', 'in:fixed_price,tip_jar'],
        'currency'            => ['required', 'string', 'size:3', 'regex:/^[a-zA-Z]{3}$/'],
        'product_description' => ['required', 'string', 'max:200'],
        'success_url'         => ['required', 'url', 'max:500'],
        'cancel_url'          => ['required', 'url', 'max:500'],

        // fixed_price: at minimum option 1 is required; 2 and 3 are optional.
        'option_1_label'  => ['required_if:mode,fixed_price', 'nullable', 'string', 'max:50'],
        'option_1_amount' => ['required_if:mode,fixed_price', 'nullable', 'numeric', 'min:0.01', 'max:999999.99'],
        'option_2_label'  => ['nullable', 'string', 'max:50'],
        'option_2_amount' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
        'option_3_label'  => ['nullable', 'string', 'max:50'],
        'option_3_amount' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],

        // tip_jar: both optional; min defaults to 1.00 when omitted.
        'min_amount'       => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
        'suggested_amount' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
    ];

    // --- Build linkData ---
    $linkData = [
        'title'               => strip_tags((string) $request->input('title')),
        'link'                => strip_tags((string) $request->input('link')),
        'mode'                => $mode,
        'currency'            => $currency,
        'product_description' => strip_tags((string) $request->input('product_description')),
        'success_url'         => trim((string) $request->input('success_url')),
        'cancel_url'          => trim((string) $request->input('cancel_url')),
    ];

    if ($mode === 'fixed_price') {
        $options = [];
        for ($i = 1; $i <= 3; $i++) {
            $label  = trim((string) $request->input("option_{$i}_label", ''));
            $amount = $request->input("option_{$i}_amount");
            if ($label === '' || $amount === null || $amount === '') {
                continue;
            }
            $options[] = [
                'label'        => strip_tags($label),
                'amount_cents' => stripe_payment_amount_to_smallest_unit((float) $amount, $currency),
            ];
        }
        $linkData['options'] = $options;
        // Legacy mirror — any pre-expansion reader that only checks
        // amount_cents will still see the first option's amount.
        if (!empty($options)) {
            $linkData['amount_cents'] = $options[0]['amount_cents'];
        }
    } else {
        // tip_jar
        $min       = $request->input('min_amount');
        $suggested = $request->input('suggested_amount');

        // Default floor of 1.00 if left blank, per spec.
        $minDecimal = ($min !== null && $min !== '') ? (float) $min : 1.00;
        $linkData['min_amount_cents'] = stripe_payment_amount_to_smallest_unit($minDecimal, $currency);
        $linkData['suggested_amount_cents'] =
            ($suggested !== null && $suggested !== '')
                ? stripe_payment_amount_to_smallest_unit((float) $suggested, $currency)
                : null;
    }

    return ['rules' => $rules, 'linkData' => $linkData];
}
