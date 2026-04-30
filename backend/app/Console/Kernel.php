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
        \App\Console\Commands\CreateTestUser::class,
        \App\Console\Commands\CompareIdsls::class,
        \App\Console\Commands\DeleteIdsls::class,
        \App\Console\Commands\FindDuplicateIdsls::class,
        \App\Console\Commands\DedupeAlokasis::class,
        \App\Console\Commands\ExportAlokasisJson::class,
    ];

    /**
     * Define the application's command schedule.
     */
    // PERFORMANCE FIX: Schedule file cleanup job
    // Run CleanupOldFiles job daily at 2:00 AM to remove old files
    // This prevents disk space bloat and maintains application performance
    protected function schedule(Schedule $schedule): void
    {
        // PERFORMANCE FIX: Schedule file cleanup job daily at 2:00 AM
        // Files older than 24 hours will be deleted from:
        // - skp_files (old SKP PDFs)
        // - surveycraft (old survey definitions)
        // - surveycraft/respond (old survey responses)
        $schedule->dailyAt('02:00')->job(new \App\Jobs\CleanupOldFiles());
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
