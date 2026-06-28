<?php

/**
 * Handles the "buymeacoffee" block.
 *
 * Storage map:
 *
 *   - title         → links.title       (optional heading)
 *   - link          → links.link        (canonical buymeacoffee.com URL,
 *                                        used for admin display)
 *   - bmc_username  → type_params JSON  (the URL slug we link to)
 *   - button_label  → type_params JSON  (custom CTA text on the button;
 *                                        defaults to "Buy me a coffee"
 *                                        in display.blade.php)
 *   - collapsed     → type_params JSON  (per-instance Start collapsed
 *                                        toggle from shared partial)
 *
 * No iframe, no external JS — just a styled <a> that opens the
 * BMC hosted checkout in a new tab on click.
 */

if (!function_exists('buymeacoffee_extract_username')) {
    /**
     * Accepts:
     *   - https://www.buymeacoffee.com/jameskoch
     *   - buymeacoffee.com/jameskoch
     *   - bmc.link/jameskoch       (BMC's short domain)
     *   - coff.ee/jameskoch         (BMC's shortest domain)
     *   - jameskoch                 (bare slug)
     *
     * BMC usernames are 3-30 chars, lowercase alphanumeric + hyphens
     * + underscores. Returns the lowercased username or null.
     */
    function buymeacoffee_extract_username(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        // Bare username?
        if (preg_match('/^[A-Za-z0-9_-]{3,30}$/', $input)) {
            return strtolower($input);
        }

        // Add scheme so parse_url works.
        if (!preg_match('#^https?://#i', $input)) {
            $input = 'https://' . ltrim($input, '/');
        }
        $parts = @parse_url($input);
        if (!is_array($parts) || empty($parts['host'])) {
            return null;
        }
        $host = strtolower($parts['host']);
        // Recognised BMC hostnames.
        $ok = preg_match('/(^|\.)(?:buymeacoffee\.com|bmc\.link|coff\.ee)$/', $host);
        if (!$ok) {
            return null;
        }
        $candidate = strtok(ltrim($parts['path'] ?? '', '/'), '/');
        if ($candidate !== false && preg_match('/^[A-Za-z0-9_-]{3,30}$/', $candidate)) {
            return strtolower($candidate);
        }
        return null;
    }
}

function handleLinkType($request, $linkType)
{
    $rules = [
        'title'        => ['nullable', 'string', 'max:100'],
        'bmc_username' => ['required', 'string', 'max:255'],
        'button_label' => ['nullable', 'string', 'max:60'],
    ];

    $username = buymeacoffee_extract_username($request->input('bmc_username'));
    if ($username === null) {
        $rules['bmc_username'][] = function ($attr, $value, $fail) {
            $fail("Couldn't read that as a Buy Me a Coffee username. Use a buymeacoffee.com/<name> URL or just the username (3-30 letters / numbers / dashes / underscores).");
        };
    }

    $title = trim((string) $request->input('title', ''));
    $label = trim((string) $request->input('button_label', ''));

    $linkData = [
        'title'        => $title === '' ? '' : strip_tags($title),
        'link'         => 'https://buymeacoffee.com/' . ($username ?? ''),
        'bmc_username' => $username,
        'button_label' => $label === '' ? '' : strip_tags($label),
        'collapsed'    => (bool) $request->input('collapsed'),
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
