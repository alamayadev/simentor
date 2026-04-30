<?php

namespace App\Jobs;

use App\Models\Skp;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadSkpToGoogleDrive implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     *
     * @param int $skpId SKP record ID
     * @param string $filePath Path to the PDF file (relative to storage)
     * @param string $fileName Filename for Google Drive
     * @param string $jenis SKP type (SKP Bulanan, SKP Tahunan (Penetapan), etc.)
     * @param string $tahun Year
     * @param string|null $bulan Month (for SKP Bulanan)
     */
    public function __construct(
        public int $skpId,
        public string $filePath,
        public string $fileName,
        public string $jenis,
        public string $tahun,
        public ?string $bulan = null
    ) {}

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        // Exponential backoff: 10 seconds, 30 seconds, 60 seconds
        return [10, 30, 60];
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleDriveService $googleDriveService): void
    {
        // Check if Google Drive is enabled
        if (!$googleDriveService->isEnabled()) {
            Log::info('Google Drive upload skipped - integration is disabled', [
                'skp_id' => $this->skpId,
                'file_name' => $this->fileName
            ]);
            return;
        }

        // Get the full file path
        $fullPath = Storage::disk('direct')->path($this->filePath);

        if (!file_exists($fullPath)) {
            Log::error('SKP file not found for Google Drive upload', [
                'skp_id' => $this->skpId,
                'file_path' => $this->filePath,
                'full_path' => $fullPath
            ]);
            return;
        }

        // Get target folder based on SKP type, year, and month
        $targetFolderId = $googleDriveService->getSkpTargetFolder(
            $this->jenis,
            $this->tahun,
            $this->bulan
        );

        if (!$targetFolderId) {
            Log::error('Failed to get target folder for SKP upload', [
                'skp_id' => $this->skpId,
                'jenis' => $this->jenis,
                'tahun' => $this->tahun,
                'bulan' => $this->bulan
            ]);

            // Re-throw exception if we haven't exhausted retries
            if ($this->attempts() < $this->tries) {
                throw new \Exception('Failed to get target folder for SKP upload');
            }
            return;
        }

        // Upload to the target folder in Google Drive
        $result = $googleDriveService->uploadFile($fullPath, $this->fileName, $targetFolderId);

        if ($result['success']) {
            Log::info('SKP file uploaded to Google Drive successfully', [
                'skp_id' => $this->skpId,
                'file_name' => $this->fileName,
                'jenis' => $this->jenis,
                'tahun' => $this->tahun,
                'bulan' => $this->bulan,
                'target_folder_id' => $targetFolderId,
                'google_drive_file_id' => $result['file_id'] ?? null,
                'google_drive_link' => $result['web_link'] ?? null
            ]);
        } else {
            Log::error('Failed to upload SKP file to Google Drive', [
                'skp_id' => $this->skpId,
                'file_name' => $this->fileName,
                'error' => $result['error'] ?? 'Unknown error'
            ]);

            // Re-throw exception if we haven't exhausted retries
            if ($this->attempts() < $this->tries) {
                throw new \Exception($result['error'] ?? 'Google Drive upload failed');
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Google Drive upload job failed after all retries', [
            'skp_id' => $this->skpId,
            'file_name' => $this->fileName,
            'jenis' => $this->jenis,
            'tahun' => $this->tahun,
            'bulan' => $this->bulan,
            'error' => $exception?->getMessage()
        ]);
    }
}
