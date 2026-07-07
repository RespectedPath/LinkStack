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
     * Stable, render-relevant columns captured per block (+ the joined
     * button `name`). Excludes volatile columns the public page never
     * renders — click_number, created_at, updated_at — so the snapshot
     * is stable and discard's re-created rows round-trip to isDirty==false.
     */
    public const BLOCK_FIELDS = [
        'id', 'user_id', 'button_id', 'link', 'title', 'type',
        'type_params', 'order', 'up_link', 'custom_css', 'custom_icon', 'name',
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

        $blocks = self::liveLinks($userId)
            ->map(fn ($l) => array_intersect_key((array) $l, array_flip(self::BLOCK_FIELDS)))
            ->all();

        return [
            'v'      => self::VERSION,
            'user'   => array_intersect_key($user->getAttributes(), array_flip(self::USER_FIELDS)),
            'blocks' => $blocks,
            // 'images' is added by publishFor() (copies of the draft
            // files at publish time) — not part of the draft snapshot.
        ];
    }

    /**
     * Rebuild the public-render inputs from a snapshot array.
     * Returns [$userinfo (object), $information (collection), $links (collection)].
     */
    public static function hydrate(array $snapshot): array
    {
        // User MODELS (not stdClass) so undefined-property access returns
        // null gracefully — the admin bar and other modules read fields
        // the snapshot omits (adminUser, email_verified_at, …), which
        // would raise "Undefined property" on a stdClass and break the
        // page in debug mode. This matches the live render exactly (it
        // passes User models too).
        $userinfo = (new User())->forceFill($snapshot['user'] ?? []);
        $userinfo->exists = true;

        $information = collect([(new User())->forceFill([
            'name'                   => $userinfo->name,
            'littlelink_name'        => $userinfo->littlelink_name,
            'littlelink_description' => $userinfo->littlelink_description,
            'theme'                  => $userinfo->theme,
        ])]);

        // Null-default every links column so a display reading one the
        // snapshot omits (click_number, timestamps) gets null, not a
        // warning — matching the live query's full-column shape.
        $linkDefaults = array_fill_keys([
            'id', 'user_id', 'button_id', 'link', 'title', 'type',
            'type_params', 'order', 'click_number', 'up_link',
            'created_at', 'updated_at', 'custom_css', 'custom_icon', 'name',
        ], null);

        $links = collect($snapshot['blocks'] ?? [])->map(function ($b) use ($linkDefaults) {
            $link = (object) array_merge($linkDefaults, (array) $b);
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
        $user = User::where('id', $userId)->first(['published_snapshot', 'has_unpublished_changes']);
        if (!$user || empty($user->published_snapshot)) {
            return false;
        }
        // Image changes are flagged explicitly (they can't be diffed from
        // the render payload); text/block changes are detected dynamically.
        if ($user->has_unpublished_changes) {
            return true;
        }
        $publishedArr = json_decode($user->published_snapshot, true);
        return self::renderKey(self::serialize($userId))
            !== self::renderKey(is_array($publishedArr) ? $publishedArr : null);
    }

    /** Publish: snapshot the current draft (incl. copying the draft
     *  images to a published location) as the published page. */
    public static function publishFor($userId): void
    {
        $snapshot = self::serialize($userId);
        if ($snapshot) {
            $snapshot['images'] = self::publishImages($userId);
        }
        User::where('id', $userId)->update([
            'published_snapshot'      => $snapshot ? json_encode($snapshot) : null,
            'has_unpublished_changes' => false,
        ]);
    }

    /** Flag an unpublished IMAGE change (called by the avatar/background
     *  upload + remove handlers). No-op until the user is on the
     *  snapshot model. */
    public static function markImageDirty($userId): void
    {
        User::where('id', $userId)
            ->whereNotNull('published_snapshot')
            ->update(['has_unpublished_changes' => true]);
    }

    /**
     * Discard: reverse-sync the live draft back to the published
     * snapshot — restore user fields, delete blocks added since publish,
     * recreate/restore the published blocks (click counts left intact),
     * and restore the images. No-op if nothing is published.
     */
    public static function discard($userId): void
    {
        $user = User::where('id', $userId)->first(['published_snapshot']);
        if (!$user || empty($user->published_snapshot)) {
            return;
        }
        $snap = json_decode($user->published_snapshot, true);
        if (!is_array($snap)) {
            return;
        }

        // 1. Restore the drafted user fields.
        $fields = array_intersect_key(
            $snap['user'] ?? [],
            array_flip(['name', 'littlelink_description', 'theme', 'theme_customization'])
        );
        User::where('id', $userId)->update($fields + ['has_unpublished_changes' => false]);

        // 2. Reverse-sync blocks: drop draft blocks added since publish,
        //    then upsert every published block (recreating deleted ones,
        //    restoring content/order). click_number is not touched.
        $blocks = collect($snap['blocks'] ?? []);
        $keepIds = $blocks->pluck('id')->filter()->values()->all();
        $del = DB::table('links')->where('user_id', $userId);
        if (!empty($keepIds)) {
            $del->whereNotIn('id', $keepIds);
        }
        $del->delete();

        $cols = ['id', 'button_id', 'link', 'title', 'custom_css', 'custom_icon', 'type', 'type_params', 'order', 'up_link'];
        foreach ($blocks as $b) {
            $row = array_intersect_key((array) $b, array_flip($cols));
            $row['user_id'] = $userId;
            DB::table('links')->updateOrInsert(['id' => $b['id'] ?? null], $row);
        }

        // 3. Restore images from the published copies.
        self::restoreImages($userId, $snap['images'] ?? []);
    }

    /**
     * Copy the current draft avatar/background to a published location
     * (assets/img/published/{uid}.ext, .../background-img/published/
     * {uid}.ext) and return the snapshot 'images' block. Public render
     * uses these copies; the editor/preview use the draft files, so a
     * re-upload in draft can't disturb what's published.
     */
    private static function publishImages($userId): array
    {
        $out = ['avatar' => null, 'background' => null];

        $draftAvatar = findAvatar($userId); // 'assets/img/{file}' or 'error.error'
        if ($draftAvatar !== 'error.error' && is_file(base_path($draftAvatar))) {
            $ext = strtolower(pathinfo($draftAvatar, PATHINFO_EXTENSION)) ?: 'jpg';
            if (!is_dir(base_path('assets/img/published'))) @mkdir(base_path('assets/img/published'), 0755, true);
            foreach (glob(base_path('assets/img/published/' . $userId . '.*')) ?: [] as $old) {
                @unlink($old);
            }
            $rel = 'assets/img/published/' . $userId . '.' . $ext;
            @copy(base_path($draftAvatar), base_path($rel));
            $out['avatar'] = $rel;
        }

        $draftBg = findBackground($userId); // '{file}' or 'error.error'
        if ($draftBg !== 'error.error' && is_file(base_path('assets/img/background-img/' . $draftBg))) {
            $ext = strtolower(pathinfo($draftBg, PATHINFO_EXTENSION)) ?: 'jpg';
            if (!is_dir(base_path('assets/img/background-img/published'))) @mkdir(base_path('assets/img/background-img/published'), 0755, true);
            foreach (glob(base_path('assets/img/background-img/published/' . $userId . '.*')) ?: [] as $old) {
                @unlink($old);
            }
            // Stored as the "filename" theme.blade appends to
            // assets/img/background-img/.
            $relFile = 'published/' . $userId . '.' . $ext;
            @copy(base_path('assets/img/background-img/' . $draftBg), base_path('assets/img/background-img/' . $relFile));
            $out['background'] = $relFile;
        }

        return $out;
    }

    /** Restore the draft image files from the published copies (discard). */
    private static function restoreImages($userId, array $images): void
    {
        // Avatar: clear draft avatars, then copy the published one back.
        foreach (glob(base_path('assets/img/' . $userId . '_*')) ?: [] as $f) {
            @unlink($f);
        }
        if (!empty($images['avatar']) && is_file(base_path($images['avatar']))) {
            $ext = strtolower(pathinfo($images['avatar'], PATHINFO_EXTENSION)) ?: 'jpg';
            @copy(base_path($images['avatar']), base_path('assets/img/' . $userId . '_' . time() . '.' . $ext));
        }

        // Background: same.
        foreach (glob(base_path('assets/img/background-img/' . $userId . '_*')) ?: [] as $f) {
            @unlink($f);
        }
        if (!empty($images['background'])) {
            $pub = base_path('assets/img/background-img/' . $images['background']);
            if (is_file($pub)) {
                $ext = strtolower(pathinfo($pub, PATHINFO_EXTENSION)) ?: 'jpg';
                @copy($pub, base_path('assets/img/background-img/' . $userId . '_' . time() . '.' . $ext));
            }
        }
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
