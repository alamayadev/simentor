<?php

namespace App\Console\Commands;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Console\Command;

class GoogleAuthorize extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'google:authorize';

    /**
     * The console command description.
     */
    protected $description = 'Authorize the application to access Google Drive using OAuth 2.0';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Google Drive OAuth 2.0 Authorization');
        $this->newLine();

        $credentialsPath = config('google.drive.oauth_credentials_path');
        $tokenPath = config('google.drive.oauth_token_path');

        // Check if credentials file exists
        if (!file_exists($credentialsPath)) {
            $this->error("OAuth credentials file not found: {$credentialsPath}");
            $this->newLine();
            $this->warn('To set up OAuth credentials:');
            $this->line('1. Go to Google Cloud Console → APIs & Services → Credentials');
            $this->line('2. Click "+ CREATE CREDENTIALS" → "OAuth client ID"');
            $this->line('3. Application type: "Desktop app"');
            $this->line('4. Download the JSON file');
            $this->line("5. Save it as: {$credentialsPath}");
            return Command::FAILURE;
        }

        $this->info("✓ Credentials file found: {$credentialsPath}");

        // Check if already authorized
        if (file_exists($tokenPath)) {
            $tokenData = json_decode(file_get_contents($tokenPath), true);
            if (isset($tokenData['refresh_token'])) {
                $this->info("✓ Already authorized! Token file exists: {$tokenPath}");
                $this->newLine();
                
                if (!$this->confirm('Do you want to re-authorize?', false)) {
                    return Command::SUCCESS;
                }
            }
        }

        try {
            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->setScopes([Drive::DRIVE]);
            $client->setAccessType('offline');
            $client->setPrompt('consent');

            // Generate authorization URL
            $authUrl = $client->createAuthUrl();

            $this->newLine();
            $this->line('─────────────────────────────────────────');
            $this->info('Step 1: Open this URL in your browser:');
            $this->newLine();
            $this->line($authUrl);
            $this->newLine();
            $this->line('─────────────────────────────────────────');
            $this->info('Step 2: Login with your Google account and authorize the app');
            $this->info('Step 3: Copy the authorization code and paste it below');
            $this->newLine();

            $authCode = $this->ask('Enter the authorization code');

            if (empty($authCode)) {
                $this->error('Authorization code is required');
                return Command::FAILURE;
            }

            // Exchange authorization code for access token
            $this->info('Exchanging authorization code for access token...');
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            if (isset($accessToken['error'])) {
                $this->error('Error: ' . ($accessToken['error_description'] ?? $accessToken['error']));
                return Command::FAILURE;
            }

            // Save the token
            // Ensure directory exists
            $tokenDir = dirname($tokenPath);
            if (!is_dir($tokenDir)) {
                mkdir($tokenDir, 0755, true);
            }

            file_put_contents($tokenPath, json_encode($accessToken, JSON_PRETTY_PRINT));

            $this->newLine();
            $this->info('✓ Authorization successful!');
            $this->info("✓ Token saved to: {$tokenPath}");
            $this->newLine();
            $this->info('🎉 You can now upload files to Google Drive!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Authorization failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
