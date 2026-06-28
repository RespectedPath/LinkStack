<?php

/**
 * Handles the "youtube_video" block.
 *
 * Storage map (UserController::saveLink at app/Http/Controllers/UserController.php
 * splits known columns from JSON):
 *
 *   - title          → links.title       (public heading; optional)
 *   - link           → links.link        (canonical YouTube URL, kept so
 *                                         the DB column has a real value
 *                                         and so admin tools that read
 *                                         link->link see something useful)
 *   - video_id       → type_params JSON  (the 11-char YouTube ID we
 *                                         render via /embed/{id})
 *   - privacy_mode   → type_params JSON  (true → youtube-nocookie.com,
 *                                         false → youtube.com)
 *   - collapsed      → type_params JSON  (per-instance Start collapsed
 *                                         toggle from the shared partial)
 *
 * No API calls. No keys. Pure iframe embed — works for any public
 * video, no quota concerns.
 */

if (!function_exists('youtube_video_extract_id')) {
    /**
     * Accepts any of:
     *   - https://www.youtube.com/watch?v=ID
     *   - https://youtu.be/ID
     *   - https://www.youtube.com/embed/ID
     *   - https://www.youtube.com/shorts/ID
     *   - https://www.youtube.com/v/ID
     *   - https://www.youtube-nocookie.com/embed/ID
     *   - a bare 11-character ID
     *
     * Returns the 11-character video ID or null if nothing valid.
     */
    function youtube_video_extract_id(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        // Already a bare ID?
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) {
            return $input;
        }

        // Make sure we can url-parse it. Add scheme if missing.
        if (!preg_match('#^https?://#i', $input)) {
            $input = 'https://' . ltrim($input, '/');
        }

        $parts = @parse_url($input);
        if (!is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '';
        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        // youtu.be/ID
        if (preg_match('/(^|\.)youtu\.be$/', $host)) {
            $id = ltrim($path, '/');
            $id = strtok($id, '/'); // strip any trailing path
            return preg_match('/^[A-Za-z0-9_-]{11}$/', (string) $id) ? $id : null;
        }

        // youtube.com or youtube-nocookie.com
        if (preg_match('/(^|\.)youtube(-nocookie)?\.com$/', $host)) {
            // watch?v=ID
            if (!empty($query['v']) && preg_match('/^[A-Za-z0-9_-]{11}$/', $query['v'])) {
                return $query['v'];
            }
            // /embed/ID, /shorts/ID, /v/ID
            if (preg_match('#^/(?:embed|shorts|v)/([A-Za-z0-9_-]{11})#', $path, $m)) {
                return $m[1];
            }
        }

        return null;
    }
}

function handleLinkType($request, $linkType)
{
    $rules = [
        'title'        => ['nullable', 'string', 'max:100'],
        'video_url'    => ['required', 'string', 'max:500'],
        'privacy_mode' => ['nullable', 'in:0,1'],
    ];

    // Pre-extract for the validation hook below — Laravel's validator
    // doesn't have a built-in "is a valid YouTube URL" rule, so we
    // attempt the parse here and bail with a clear error if it fails.
    $videoId = youtube_video_extract_id($request->input('video_url'));
    if ($videoId === null) {
        // Force validation to fail on `video_url` with a custom message
        // by adding a Closure rule.
        $rules['video_url'][] = function ($attr, $value, $fail) {
            $fail("Couldn't read that as a YouTube video URL. Paste a youtu.be/… or youtube.com/watch?v=… link.");
        };
    }

    $title = trim((string) $request->input('title', ''));

    $linkData = [
        'title'        => $title === '' ? '' : strip_tags($title),
        'link'         => 'https://youtube.com/watch?v=' . ($videoId ?? ''),
        'video_id'     => $videoId,
        'privacy_mode' => (bool) $request->input('privacy_mode'),
        'collapsed'    => (bool) $request->input('collapsed'),
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
