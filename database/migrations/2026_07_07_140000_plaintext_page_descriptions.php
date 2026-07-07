<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The page description became a plain-text tagline (the Basics editor is now a
 * plain textarea, not CKEditor). Normalise every existing description — both
 * the live row and the published snapshot copy — to plain text so no legacy
 * markup renders, and so the draft/published pair stays byte-identical (no
 * user shows as "unpublished changes" purely because of this migration).
 *
 * Idempotent: strip_tags() on already-plain text is a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('users')->select('id', 'littlelink_description', 'published_snapshot')->cursor() as $u) {
            $updates = [];

            if ($u->littlelink_description !== null) {
                $plain = trim(strip_tags($u->littlelink_description));
                if ($plain !== $u->littlelink_description) {
                    $updates['littlelink_description'] = $plain;
                }
            }

            if ($u->published_snapshot !== null) {
                $snap = json_decode($u->published_snapshot, true);
                if (isset($snap['user']['littlelink_description'])) {
                    $plain = trim(strip_tags($snap['user']['littlelink_description']));
                    if ($plain !== $snap['user']['littlelink_description']) {
                        $snap['user']['littlelink_description'] = $plain;
                        $updates['published_snapshot'] = json_encode($snap, JSON_UNESCAPED_SLASHES);
                    }
                }
            }

            if ($updates) {
                DB::table('users')->where('id', $u->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Irreversible: the stripped markup is intentionally discarded.
    }
};
