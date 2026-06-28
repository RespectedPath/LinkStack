<?php

/**
 * Handles the "spotify" block.
 *
 * Spotify supports six embeddable content types, all with the same
 * iframe URL shape:
 *   https://open.spotify.com/embed/<type>/<id>
 *
 * where <type> ∈ { track, album, playlist, artist, show, episode }
 * and <id>   is a 22-character base62 ID.
 *
 * Storage map:
 *
 *   - title         → links.title       (optional heading)
 *   - link          → links.link        (canonical open.spotify.com URL,
 *                                        for admin display)
 *   - content_type  → type_params JSON  (one of the six allowed types)
 *   - content_id    → type_params JSON  (22-char Spotify ID)
 *   - collapsed     → type_params JSON  (shared Start-collapsed flag)
 *
 * Player height is picked at render time based on content_type
 * (tracks/episodes get a compact ~152px; albums/playlists/shows/
 * artists get the taller ~352px with tracklist).
 *
 * Privacy note: Spotify does not offer a nocookie embed domain.
 * Iframes from open.spotify.com will set cookies once the visitor
 * interacts. There's no way around this short of not using the
 * official player.
 */

if (!function_exists('spotify_extract_resource')) {
    /**
     * Accepts:
     *   - https://open.spotify.com/track/<id>?si=…
     *   - https://open.spotify.com/intl-en/track/<id>           (locale prefix)
     *   - https://open.spotify.com/embed/playlist/<id>          (already an embed URL)
     *   - spotify:track:<id>                                    (URI scheme)
     *   - <22-char-id> in combination with a type chosen elsewhere
     *
     * Returns ['type' => 'track'|…, 'id' => '<22-char>'] or null.
     */
    function spotify_extract_resource(?string $input): ?array
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        $allowedTypes = ['track', 'album', 'playlist', 'artist', 'show', 'episode'];

        // spotify:TYPE:ID URI format
        if (preg_match('/^spotify:(' . implode('|', $allowedTypes) . '):([A-Za-z0-9]{22})$/', $input, $m)) {
            return ['type' => $m[1], 'id' => $m[2]];
        }

        // URL paths: open.spotify.com/[intl-xx/]embed?/TYPE/ID
        if (!preg_match('#^https?://#i', $input)) {
            $input = 'https://' . ltrim($input, '/');
        }
        $parts = @parse_url($input);
        if (!is_array($parts) || empty($parts['host'])) {
            return null;
        }
        $host = strtolower($parts['host']);
        if (!preg_match('/(^|\.)open\.spotify\.com$/', $host)) {
            return null;
        }
        $path = ltrim($parts['path'] ?? '', '/');
        // Strip optional intl-xx/ and embed/ prefixes.
        $path = preg_replace('#^intl-[a-z]{2,5}/#i', '', $path);
        $path = preg_replace('#^embed/#i', '', $path);
        $segments = explode('/', $path);
        if (count($segments) < 2) {
            return null;
        }
        [$type, $id] = $segments;
        if (!in_array($type, $allowedTypes, true)) {
            return null;
        }
        if (!preg_match('/^[A-Za-z0-9]{22}$/', $id)) {
            return null;
        }
        return ['type' => $type, 'id' => $id];
    }
}

function handleLinkType($request, $linkType)
{
    $rules = [
        'title'       => ['nullable', 'string', 'max:100'],
        'spotify_url' => ['required', 'string', 'max:500'],
    ];

    $parsed = spotify_extract_resource($request->input('spotify_url'));
    if ($parsed === null) {
        $rules['spotify_url'][] = function ($attr, $value, $fail) {
            $fail("Couldn't read that as a Spotify URL. Open a track / album / playlist / podcast in Spotify, click 'Share → Copy link', and paste it here.");
        };
    }

    $title = trim((string) $request->input('title', ''));

    $linkData = [
        'title'        => $title === '' ? '' : strip_tags($title),
        'link'         => $parsed
            ? 'https://open.spotify.com/' . $parsed['type'] . '/' . $parsed['id']
            : '',
        'content_type' => $parsed['type'] ?? null,
        'content_id'   => $parsed['id'] ?? null,
        'collapsed'    => (bool) $request->input('collapsed'),
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
