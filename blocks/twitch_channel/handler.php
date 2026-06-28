<?php

/**
 * Handles the "twitch_channel" block.
 *
 * Storage map (UserController::saveLink splits known columns from
 * extra keys, which become JSON in type_params):
 *
 *   - title        → links.title       (optional heading)
 *   - link         → links.link        (canonical twitch.tv URL,
 *                                       kept so admin tooling that
 *                                       shows link->link has something
 *                                       useful)
 *   - channel_name → type_params JSON  (the URL slug we embed)
 *   - collapsed    → type_params JSON  (per-instance Start collapsed
 *                                       toggle from shared partial)
 *
 * Twitch player iframes REQUIRE a `parent` query param matching the
 * hostname the iframe is loaded from — that's set at render time
 * in display.blade.php, not stored. So the only thing this handler
 * needs to extract is the channel name itself.
 */

if (!function_exists('twitch_channel_extract_name')) {
    /**
     * Accepts any of:
     *   - https://www.twitch.tv/jameskoch
     *   - http://twitch.tv/jameskoch/about
     *   - twitch.tv/jameskoch
     *   - jameskoch  (bare channel name)
     *
     * Returns the channel name (4-25 chars, alphanumeric + underscore
     * per Twitch's account-name rules) or null if nothing valid.
     */
    function twitch_channel_extract_name(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        // Already a bare channel name?
        if (preg_match('/^[A-Za-z0-9_]{4,25}$/', $input)) {
            return strtolower($input);
        }

        // Add scheme if missing so parse_url works.
        if (!preg_match('#^https?://#i', $input)) {
            $input = 'https://' . ltrim($input, '/');
        }

        $parts = @parse_url($input);
        if (!is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        if (!preg_match('/(^|\.)twitch\.tv$/', $host)) {
            return null;
        }

        $path = ltrim($parts['path'] ?? '', '/');
        $candidate = strtok($path, '/'); // first segment only
        if ($candidate !== false && preg_match('/^[A-Za-z0-9_]{4,25}$/', $candidate)) {
            return strtolower($candidate);
        }

        return null;
    }
}

function handleLinkType($request, $linkType)
{
    $rules = [
        'title'   => ['nullable', 'string', 'max:100'],
        'channel' => ['required', 'string', 'max:255'],
    ];

    $channel = twitch_channel_extract_name($request->input('channel'));
    if ($channel === null) {
        // Push a closure rule so validation fails with a clear message.
        $rules['channel'][] = function ($attr, $value, $fail) {
            $fail("Couldn't read that as a Twitch channel. Use a twitch.tv/<channel> URL or just the channel name (4-25 letters / numbers / underscores).");
        };
    }

    $title = trim((string) $request->input('title', ''));

    $linkData = [
        'title'        => $title === '' ? '' : strip_tags($title),
        'link'         => 'https://twitch.tv/' . ($channel ?? ''),
        'channel_name' => $channel,
        'collapsed'    => (bool) $request->input('collapsed'),
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
