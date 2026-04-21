<?php

/**
 * Handles the logic for "stripe_payment" link type.
 *
 * Storage mapping (UserController::saveLink splits between columns and
 * type_params JSON at app/Http/Controllers/UserController.php:230-292):
 *
 *   - title              → links.title        (public heading)
 *   - link               → links.link         (public CTA button label)
 *   - amount_cents       → type_params JSON   (public; rendered as formatted amount)
 *   - currency           → type_params JSON   (public)
 *   - product_description → type_params JSON  (public; shown on Stripe checkout)
 *   - success_url        → type_params JSON   (public)
 *   - cancel_url         → type_params JSON   (public)
 *
 * No Stripe secret or connected account ID is written here. Those
 * live respectively in .env and on the user row; the controller
 * reads them server-side when creating a Checkout Session.
 *
 * button_id is intentionally omitted; saveLink defaults it to 1.
 */
function handleLinkType($request, $linkType) {

    $rules = [
        'title'               => ['required', 'string', 'max:100'],
        'link'                => ['required', 'string', 'max:50'],
        'amount'              => ['required', 'numeric', 'min:0.50', 'max:999999.99'],
        'currency'            => ['required', 'string', 'size:3', 'regex:/^[a-zA-Z]{3}$/'],
        'product_description' => ['required', 'string', 'max:200'],
        'success_url'         => ['required', 'url', 'max:500'],
        'cancel_url'          => ['required', 'url', 'max:500'],
    ];

    // Convert the human-friendly decimal input to Stripe's smallest-unit
    // integer representation.
    $amountCents = (int) round(((float) $request->input('amount', 0)) * 100);

    $linkData = [
        'title'               => strip_tags((string) $request->input('title')),
        'link'                => strip_tags((string) $request->input('link')),
        'amount_cents'        => $amountCents,
        'currency'            => strtolower(trim((string) $request->input('currency'))),
        'product_description' => strip_tags((string) $request->input('product_description')),
        'success_url'         => trim((string) $request->input('success_url')),
        'cancel_url'          => trim((string) $request->input('cancel_url')),
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
