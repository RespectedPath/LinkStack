<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Storage safety net (item: cost/abuse hardening).
 *
 * Uploaded avatars + backgrounds are bounded by design — fixed slots,
 * 2 MB cap, replace-on-upload, and now purge-on-account-deletion. This
 * command is the belt-and-suspenders: it finds (and optionally removes)
 * orphaned upload files whose owning user no longer exists, and reports
 * any user holding more than one file per slot (which would signal a
 * replace-on-upload regression).
 *
 * Report-only by default — pass --prune to actually delete orphans.
 * Safe to schedule (e.g. weekly) once you're comfortable with the
 * report output.
 */
class StorageReconcile extends Command
{
    protected $signature = 'storage:reconcile {--prune : Delete orphaned files (default: report only)}';

    protected $description = 'Report/remove orphaned uploaded images whose user no longer exists; flag per-user accumulation.';

    public function handle(): int
    {
        $prune = (bool) $this->option('prune');

        // Valid user ids as a fast lookup set.
        $validIds = array_flip(array_map('strval', User::pluck('id')->all()));

        // Avatar dir is SHARED with static assets, so match only the
        // exact upload naming "<id>_<timestamp>.<ext>" (underscore +
        // all-digit timestamp). This can never match static files like
        // "404.png" or "logo.svg". The background dir is user-only, so
        // the looser "<id>[_suffix].<ext>" is safe there.
        $targets = [
            'avatar' => [
                'dir'     => base_path('assets/img'),
                'pattern' => '/^(\d+)_\d+\.\w+$/i',
            ],
            'background' => [
                'dir'     => base_path('assets/img/background-img'),
                'pattern' => '/^(\d+)(?:_\w+)?\.\w+$/i',
            ],
        ];

        $orphanCount = 0;
        $removed = 0;
        $perUser = []; // [label][id] => count

        foreach ($targets as $label => $t) {
            if (!is_dir($t['dir'])) {
                continue;
            }
            foreach (scandir($t['dir']) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                if (!preg_match($t['pattern'], $entry, $m)) {
                    continue;
                }
                $id = $m[1];
                $perUser[$label][$id] = ($perUser[$label][$id] ?? 0) + 1;

                if (!isset($validIds[$id])) {
                    $orphanCount++;
                    $this->line(($prune ? 'removing' : 'orphan') . " {$label}: {$entry} (no user #{$id})");
                    if ($prune) {
                        $full = $t['dir'] . '/' . $entry;
                        if (is_file($full) && @unlink($full)) {
                            $removed++;
                        }
                    }
                }
            }
        }

        // Accumulation report — replace-on-upload should keep this at 1.
        $accum = 0;
        foreach ($perUser as $label => $ids) {
            foreach ($ids as $id => $count) {
                if ($count > 1 && isset($validIds[$id])) {
                    $accum++;
                    $this->warn("accumulation {$label}: user #{$id} has {$count} files (expected 1)");
                }
            }
        }

        if ($prune) {
            $this->info("storage:reconcile — {$orphanCount} orphan(s) found, {$removed} removed; {$accum} accumulation warning(s).");
        } else {
            $this->info("storage:reconcile (report only) — {$orphanCount} orphan(s), {$accum} accumulation warning(s). Re-run with --prune to delete orphans.");
        }

        return self::SUCCESS;
    }
}
