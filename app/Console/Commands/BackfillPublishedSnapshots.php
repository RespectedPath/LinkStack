<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PublishedPage;
use Illuminate\Console\Command;

/**
 * Draft/publish cutover (DRAFT-PUBLISH-PLAN.md Phase 2): give every
 * existing user a published snapshot of their CURRENT page, so the
 * public render (which now reads the snapshot) shows exactly what it
 * shows today — and from then on their edits are draft-until-Publish.
 * Idempotent: skips users who already have a snapshot unless --force.
 */
class BackfillPublishedSnapshots extends Command
{
    protected $signature = 'pages:backfill-snapshots {--force : re-snapshot users who already have one}';

    protected $description = 'Snapshot every user\'s current page as its published version (draft/publish cutover).';

    public function handle()
    {
        $force = (bool) $this->option('force');
        $done = 0;
        $skipped = 0;

        User::select('id', 'published_snapshot')->orderBy('id')->chunk(200, function ($users) use (&$done, &$skipped, $force) {
            foreach ($users as $u) {
                if (!$force && !empty($u->published_snapshot)) {
                    $skipped++;
                    continue;
                }
                PublishedPage::publishFor($u->id);
                $done++;
            }
        });

        $this->info("Backfilled {$done} user(s); skipped {$skipped} already-published.");
        return 0;
    }
}
