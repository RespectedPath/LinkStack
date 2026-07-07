<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Draft / Publish (DRAFT-PUBLISH-PLAN.md). The live DB rows are the
// DRAFT (the editor keeps writing to them). `published_snapshot` holds
// the PUBLISHED page state that the public /@handle renders from, so
// edits never reach the public page until an explicit Publish.
//
//   published_snapshot        JSON: { user: {...fields}, blocks: [...],
//                             images: { avatar, background } }. Null =
//                             never published -> public renders live
//                             (until the one-time backfill / first publish).
//   has_unpublished_changes   set on any editor save, cleared on
//                             publish/discard; drives the editor banner.
//
// Columns on the existing users table (no new table), so the RLS policy
// on users covers them unchanged.

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Text, not native json(), for SQLite dev compatibility
            // (matches theme_customization).
            $table->text('published_snapshot')->nullable()->after('theme_customization');
            $table->boolean('has_unpublished_changes')->default(false)->after('published_snapshot');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['published_snapshot', 'has_unpublished_changes']);
        });
    }
};
