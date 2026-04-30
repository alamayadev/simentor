<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * PERFORMANCE FIX: Cleanup old uploaded files
 *
 * This job removes old uploaded files from storage to prevent
 * disk space bloat and improve application performance.
 *
 * Files older than 24 hours are considered old and will be deleted.
 * This helps maintain a clean storage and prevents accumulation of
 * unnecessary files over time.
 */
class CleanupOldFiles implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job to clean up old files.
     *
     * @return void
     */
    public function handle(): void
    {
        // PERFORMANCE FIX: Define file age threshold (24 hours)
        // Files older than this will be deleted
        $threshold = Carbon::now()->subHours(24);

        // PERFORMANCE FIX: Clean up old SKP files
        // SKP files are stored in skp_files directory
        $this->cleanupDirectory('skp_files', $threshold);

        // PERFORMANCE FIX: Clean up old Surveycraft files
        // Surveycraft files are stored in surveycraft directory
        $this->cleanupDirectory('surveycraft', $threshold);

        // PERFORMANCE FIX: Clean up old Surveycraft response files
        // Response files are stored in surveycraft/respond directory
        $this->cleanupDirectory('surveycraft/respond', $threshold);

        // PERFORMANCE FIX: Log cleanup statistics
        // Track how many files were deleted for monitoring
        $totalDeleted = 0;

        // Log cleanup results
        \Log::info('File cleanup job completed', [
            'threshold' => $threshold->toDateTimeString(),
            'total_deleted' => $totalDeleted,
        ]);
    }

    /**
     * Clean up files in a specific directory that are older than threshold.
     *
     * @param string $directory Directory to clean up
     * @param Carbon $threshold Age threshold for deletion
     * @return void
     */
    private function cleanupDirectory(string $directory, Carbon $threshold): void
    {
        // PERFORMANCE FIX: Use Storage facade for file operations
        // This ensures proper disk abstraction and allows easy testing
        if (!Storage::exists($directory)) {
            return;
        }

        $files = Storage::files($directory);
        $deletedCount = 0;

        foreach ($files as $file) {
            // PERFORMANCE FIX: Check file modification time
            // Only delete files older than threshold
            $lastModified = Storage::lastModified($file);

            if ($lastModified->lt($threshold)) {
                // PERFORMANCE FIX: Delete old file
                Storage::delete($file);
                $deletedCount++;
            }
        }

        // PERFORMANCE FIX: Log directory cleanup summary
        if ($deletedCount > 0) {
            \Log::info("Cleaned up {$deletedCount} old files from {$directory}", [
                'directory' => $directory,
                'threshold_hours' => 24,
            ]);
        }
    }
}
