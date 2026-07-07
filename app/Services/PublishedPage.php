<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Draft / Publish (DRAFT-PUBLISH-PLAN.md).
 *
 * The live DB rows are the DRAFT. A serialized snapshot in
 * users.published_snapshot is the PUBLISHED page. serialize() captures
 * the current draft; hydrate() rebuilds the exact inputs the public
 * bio-page view consumes ($userinfo, $information, $links) — so the
 * public render reads the snapshot without touching the live rows, and
 * an edit never reaches /@handle until Publish.
 *
 * hydrate() must reproduce UserController@littlelink's inputs exactly:
 * the same $links shape (links.* + buttons.name) with each row's
 * type_params decoded and merged onto the object.
 */
class PublishedPage
{
    public const VERSION = 1;

    /** users columns the public render reads. */
    public const USER_FIELDS = [
        'id', 'name', 'littlelink_name', 'littlelink_description',
        'theme', 'role', 'block', 'google_analytics_id', 'theme_customization',
    ];

    /**
     * The exact links query the public render uses — one place so
     * serialize() and the live render can't drift.
     */
    public static function liveLinks($userId)
    {
        return DB::table('links')
            ->join('buttons', 'buttons.id', '=', 'links.button_id')
            ->select('links.*', 'buttons.name')
            ->where('user_id', $userId)
            ->orderBy('up_link', 'asc')
            ->orderBy('order', 'asc')
            ->get();
    }

    /**
     * Serialize the user's CURRENT draft (live DB) into the snapshot
     * shape. Returns null if the user doesn't exist.
     */
    public static function serialize($userId): ?array
    {
        $user = User::where('id', $userId)->first(self::USER_FIELDS);
        if (!$user) {
            return null;
        }

        $blocks = self::liveLinks($userId)->map(fn ($l) => (array) $l)->all();

        return [
            'v'      => self::VERSION,
            'user'   => array_intersect_key($user->getAttributes(), array_flip(self::USER_FIELDS)),
            'blocks' => $blocks,
            'images' => [
                // Draft filenames now; Phase 3 copies these to a published
                // location on publish so re-uploads can't disturb them.
                'avatar'     => findAvatar($userId),
                'background' => findBackground($userId),
            ],
        ];
    }

    /**
     * Rebuild the public-render inputs from a snapshot array.
     * Returns [$userinfo (object), $information (collection), $links (collection)].
     */
    public static function hydrate(array $snapshot): array
    {
        $userinfo = (object) ($snapshot['user'] ?? []);

        // $information: a 1-item collection of the head/title fields.
        $information = collect([(object) [
            'name'                   => $userinfo->name ?? null,
            'littlelink_name'        => $userinfo->littlelink_name ?? null,
            'littlelink_description' => $userinfo->littlelink_description ?? null,
            'theme'                  => $userinfo->theme ?? null,
        ]]);

        $links = collect($snapshot['blocks'] ?? [])->map(function ($b) {
            $link = (object) $b;
            // Same type_params decode+merge the controller does, so every
            // block display sees the properties it expects.
            if (!empty($link->type_params)) {
                $tp = json_decode($link->type_params, true);
                if (is_array($tp)) {
                    foreach ($tp as $k => $v) {
                        $link->$k = $v;
                    }
                }
            }
            return $link;
        });

        return [$userinfo, $information, $links];
    }

    /**
     * The LIVE (draft) render inputs — the exact shape
     * UserController@littlelink built before draft/publish. One place so
     * the live render, the preview, and serialize() can't drift.
     * Returns [$userinfo (User model), $information (collection),
     * $links (collection)].
     */
    public static function liveInputs($id): array
    {
        $userinfo = User::where('id', $id)->first(self::USER_FIELDS);
        $information = User::select('name', 'littlelink_name', 'littlelink_description', 'theme')
            ->where('id', $id)->get();
        $links = self::liveLinks($id);
        foreach ($links as $link) {
            if (!empty($link->type_params)) {
                $tp = json_decode($link->type_params, true);
                if (is_array($tp)) {
                    foreach ($tp as $k => $v) {
                        $link->$k = $v;
                    }
                }
            }
        }
        return [$userinfo, $information, $links];
    }

    /**
     * The render-driving parts of a snapshot (user fields + blocks) as a
     * stable string. Images are excluded — they aren't drafted until
     * Phase 3, so they must not count as an unpublished change.
     */
    private static function renderKey(?array $snap): string
    {
        return json_encode([$snap['user'] ?? null, $snap['blocks'] ?? null]);
    }

    /**
     * True when the current draft differs from what's published. False
     * when nothing is published yet (the page still renders live, so
     * there's no draft/published split to be "dirty" against).
     */
    public static function isDirty($userId): bool
    {
        $published = User::where('id', $userId)->value('published_snapshot');
        if (empty($published)) {
            return false;
        }
        $publishedArr = json_decode($published, true);
        return self::renderKey(self::serialize($userId))
            !== self::renderKey(is_array($publishedArr) ? $publishedArr : null);
    }

    /** Publish: snapshot the current draft as the published page. */
    public static function publishFor($userId): void
    {
        $snapshot = self::serialize($userId);
        User::where('id', $userId)->update([
            'published_snapshot'      => $snapshot ? json_encode($snapshot) : null,
            'has_unpublished_changes' => false,
        ]);
    }

    /**
     * Give a user a published snapshot of their CURRENT state if they
     * don't have one — so their public page becomes snapshot-backed and
     * their edits become draft. No-op once set. Called on editor load
     * to onboard anyone the backfill missed (or brand-new users).
     */
    public static function ensureSnapshot($userId): void
    {
        if (empty(User::where('id', $userId)->value('published_snapshot'))) {
            self::publishFor($userId);
        }
    }
}
