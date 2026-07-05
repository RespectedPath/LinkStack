<?php

/**
 * Handles the logic for "text" link type.
 * 
 * @param \Illuminate\Http\Request $request The incoming request.
 * @param mixed $linkType The link type information.
 * @return array The prepared link data.
 */
function handleLinkType($request, $linkType) {

    $rules = [
        'text' => [
            'required',
            'string',
            'max:5000',
        ],
    ];

    // Sanitize the text input with HTMLPurifier (strips event-handler
    // attributes, unsafe protocols, and any tag/attr not on the allowlist
    // — the old strip_tags combo left on* handlers intact = stored XSS).
    $sanitizedText = purify_user_html($request->text);

    // Alignment is per-instance — defaults to center; constrained to
    // the three valid values. Non-column linkData keys auto-route to
    // the type_params JSON column via UserController::saveLink.
    $alignment = (string) $request->input('alignment', 'center');
    if (!in_array($alignment, ['left', 'center', 'right'], true)) {
        $alignment = 'center';
    }

    // Prepare the link data
    $linkData = [
        'title'     => $sanitizedText,
        'button_id' => "93", // predefined ID for a "text" button
        'alignment' => $alignment,
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}