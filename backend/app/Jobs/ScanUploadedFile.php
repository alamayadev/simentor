<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Sunspikes\ClamavValidator\ClamavValidator;

class ScanUploadedFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The file path to scan.
     *
     * @var string
     */
    protected $filePath;

    /**
     * The original filename.
     *
     * @var string
     */
    protected $originalName;

    /**
     * The user who uploaded the file.
     *
     * @var int|null
     */
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param string $filePath
     * @param string $originalName
     * @param int|null $userId
     * @return void
     */
    public function __construct(string $filePath, string $originalName, ?int $userId = null)
    {
        $this->filePath = $filePath;
        $this->originalName = $originalName;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Check if ClamAV validation is skipped
            if (config('clamav.skip_validation', false)) {
                Log::info('ClamAV validation skipped', [
                    'file' => $this->filePath,
                    'original_name' => $this->originalName,
                    'user_id' => $this->userId
                ]);
                return;
            }

            // Get full file path
            $fullPath = Storage::disk('local')->path($this->filePath);
            
            // Check if file exists
            if (!file_exists($fullPath)) {
                Log::warning('File not found for virus scanning', [
                    'file' => $this->filePath,
                    'full_path' => $fullPath,
                    'original_name' => $this->originalName,
                    'user_id' => $this->userId
                ]);
                return;
            }

            // Initialize ClamAV validator
            $clamavValidator = new ClamavValidator();
            
            // Scan the file
            $isClean = $clamavValidator->validate($this->filePath);

            if ($isClean) {
                Log::info('File scanned successfully - clean', [
                    'file' => $this->filePath,
                    'original_name' => $this->originalName,
                    'user_id' => $this->userId
                ]);
            } else {
                // File is infected - delete it
                Storage::disk('local')->delete($this->filePath);
                
                Log::alert('Infected file detected and deleted', [
                    'file' => $this->filePath,
                    'original_name' => $this->originalName,
                    'user_id' => $this->userId,
                    'action' => 'deleted'
                ]);

                // Optionally, you could notify administrators here
                // or implement additional security measures
            }

        } catch (\Exception $e) {
            Log::error('Virus scanning failed', [
                'file' => $this->filePath,
                'original_name' => $this->originalName,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // For security reasons, if scanning fails, we might want to delete the file
            // This depends on your security policy
            if (config('clamav.delete_on_scan_failure', false)) {
                Storage::disk('local')->delete($this->filePath);
                
                Log::warning('File deleted due to scan failure', [
                    'file' => $this->filePath,
                    'original_name' => $this->originalName,
                    'user_id' => $this->userId
                ]);
            }
        }
    }

    /**
     * The job failed to process.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception): void
    {
        Log::error('Virus scanning job failed', [
            'file' => $this->filePath,
            'original_name' => $this->originalName,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}