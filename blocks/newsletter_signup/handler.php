<?php

/**
 * Handles the logic for "newsletter_signup" link type.
 *
 * Storage mapping (UserController::saveLink at
 * app/Http/Controllers/UserController.php:230-292 does the splitting):
 *
 *   - title    → links.title        (public heading)
 *   - link     → links.link         (public CTA button label)
 *   - api_key  → merged into links.type_params JSON (SERVER-SIDE ONLY)
 *   - list_id  → merged into links.type_params JSON (SERVER-SIDE ONLY)
 *
 * display.blade.php is intentionally ignorant of type_params; only
 * NewsletterSignupController reads the Mailchimp credentials.
 * LinkTypeViewController::getParamForm decodes type_params back into
 * form.blade.php variables on edit ($api_key, $list_id).
 *
 * button_id is intentionally omitted; saveLink defaults it to 1.
 *
 * @param \Illuminate\Http\Request $request
 * @param mixed $linkType
 * @return array
 */
function handleLinkType($request, $linkType) {

    $rules = [
        'title'   => ['required', 'string', 'max:100'],
        'link'    => ['required', 'string', 'max:50'],
        // Mailchimp API keys look like "<hex>-<dc>", e.g. abc123-us1
        'api_key' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9]+-[A-Za-z0-9]+$/'],
        'list_id' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9]+$/'],
    ];

    $linkData = [
        'title'   => strip_tags((string) $request->input('title')),
        'link'    => strip_tags((string) $request->input('link')),
        'api_key' => trim((string) $request->input('api_key')),
        'list_id' => trim((string) $request->input('list_id')),
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
