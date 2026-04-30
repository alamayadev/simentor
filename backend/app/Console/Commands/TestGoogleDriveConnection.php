<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class TestGoogleDriveConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:test-drive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Google Drive connection and configuration';

    /**
     * Execute the console command.
     */
    public function handle(GoogleDriveService $googleDriveService): int
    {
        $this->info('Testing Google Drive connection...');
        $this->newLine();

        // Check if enabled
        if (!$googleDriveService->isEnabled()) {
            $this->error('Google Drive integration is disabled.');
            $this->warn('Set GOOGLE_DRIVE_ENABLED=true in your .env file.');
            return Command::FAILURE;
        }

        $this->info('✓ Google Drive integration is enabled');

        // Check credentials file
        $credentialsPath = config('google.drive.credentials_path');
        if (!file_exists($credentialsPath)) {
            $this->error("Credentials file not found: {$credentialsPath}");
            return Command::FAILURE;
        }

        $this->info("✓ Credentials file found: {$credentialsPath}");

        // Check folder ID
        $folderId = config('google.drive.folder_id');
        if (empty($folderId)) {
            $this->error('Folder ID not configured. Set GOOGLE_DRIVE_FOLDER_ID in .env');
            return Command::FAILURE;
        }

        $this->info("✓ Folder ID configured: {$folderId}");

        // Test connection
        $this->newLine();
        $this->info('Connecting to Google Drive...');

        try {
            $result = $googleDriveService->testConnection();

            if ($result['success']) {
                $this->newLine();
                $this->info('✓ ' . $result['message']);
                $this->newLine();
                $this->info('🎉 Google Drive connection is working correctly!');
                return Command::SUCCESS;
            } else {
                $errorLog = storage_path('logs/google-drive-test.txt');
                file_put_contents($errorLog, json_encode($result, JSON_PRETTY_PRINT));
                $this->newLine();
                $this->line('ERROR written to: ' . $errorLog);
                $this->newLine();
                $this->warn('Make sure you have:');
                $this->warn('  1. Enabled Google Drive API in Google Cloud Console');
                $this->warn('  2. Shared the folder with the service account email as Editor');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $errorLog = storage_path('logs/google-drive-test.txt');
            file_put_contents($errorLog, $e->getMessage() . "\n\n" . $e->getTraceAsString());
            $this->newLine();
            $this->error('Exception log written to: ' . $errorLog);
            return Command::FAILURE;
        }
    }
}
