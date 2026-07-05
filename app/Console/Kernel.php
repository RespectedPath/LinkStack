<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Weekly storage safety net — prunes orphaned upload files
        // (avatars/backgrounds whose owning user was deleted) and logs
        // any per-user accumulation. Account deletion already purges
        // files inline (purge_user_uploads); this catches anything that
        // slips through a missed path or a future regression. Runs
        // Sunday 04:00 server time. See app/Console/Commands/
        // StorageReconcile.php and PRE-DEPLOY-AUDIT.md.
        //
        // NB: requires the Laravel scheduler cron entry on the server:
        //   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
        $schedule->command('storage:reconcile --prune')
                 ->weeklyOn(0, '4:00')
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
