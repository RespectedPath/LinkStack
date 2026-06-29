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

    // Sanitize the text input
    $sanitizedText = $request->text;
    $sanitizedText = strip_tags($sanitizedText, '<a><p><strong><i><ul><ol><li><blockquote><h2><h3><h4>');
    $sanitizedText = preg_replace("/<a([^>]*)>/i", "<a $1 rel=\"noopener noreferrer nofollow\">", $sanitizedText);
    
    // Assuming strip_tags_except_allowed_protocols is a custom function defined elsewhere
    // This function should sanitize the text further by removing all tags except those allowed
    // and ensuring all protocols in href attributes are safe.
    $sanitizedText = strip_tags_except_allowed_protocols($sanitizedText);

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