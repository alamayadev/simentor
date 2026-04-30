<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected ?Client $client = null;
    protected ?Drive $driveService = null;

    /**
     * Check if Google Drive integration is enabled
     */
    public function isEnabled(): bool
    {
        return config('google.drive.enabled', false);
    }

    /**
     * Get the Google Client instance
     */
    protected function getClient(): ?Client
    {
        if ($this->client) {
            return $this->client;
        }

        $authType = config('google.drive.auth_type', 'oauth');

        try {
            $this->client = new Client();
            $this->client->setScopes([Drive::DRIVE]);
            $this->client->setAccessType('offline');

            if ($authType === 'oauth') {
                return $this->configureOAuthClient();
            } else {
                return $this->configureServiceAccountClient();
            }
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Client', [
                'error' => $e->getMessage(),
                'auth_type' => $authType
            ]);
            return null;
        }
    }

    /**
     * Configure client for OAuth 2.0 authentication
     */
    protected function configureOAuthClient(): ?Client
    {
        $credentialsPath = config('google.drive.oauth_credentials_path');
        $tokenPath = config('google.drive.oauth_token_path');

        if (!file_exists($credentialsPath)) {
            Log::error('OAuth credentials file not found', [
                'path' => $credentialsPath
            ]);
            return null;
        }

        if (!file_exists($tokenPath)) {
            Log::error('OAuth token file not found. Run: php artisan google:authorize', [
                'path' => $tokenPath
            ]);
            return null;
        }

        $this->client->setAuthConfig($credentialsPath);

        // Load token
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $this->client->setAccessToken($accessToken);

        // Refresh token if expired
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                
                // Check if the refresh was successful
                if (isset($newToken['error'])) {
                    Log::error('Failed to refresh Google OAuth token', [
                        'error' => $newToken['error'],
                        'error_description' => $newToken['error_description'] ?? 'No description'
                    ]);
                    return null;
                }

                // Save the new token
                file_put_contents($tokenPath, json_encode($newToken, JSON_PRETTY_PRINT));
                
                Log::info('Google OAuth token refreshed');
            } else {
                Log::error('OAuth token expired and no refresh token available. Run: php artisan google:authorize');
                return null;
            }
        }

        return $this->client;
    }

    /**
     * Configure client for Service Account authentication
     */
    protected function configureServiceAccountClient(): ?Client
    {
        $credentialsPath = config('google.drive.credentials_path');

        if (!file_exists($credentialsPath)) {
            Log::error('Service account credentials file not found', [
                'path' => $credentialsPath
            ]);
            return null;
        }

        $this->client->setAuthConfig($credentialsPath);
        
        return $this->client;
    }

    /**
     * Get the Google Drive service instance
     */
    protected function getDriveService(): ?Drive
    {
        if ($this->driveService) {
            return $this->driveService;
        }

        $client = $this->getClient();
        if (!$client) {
            return null;
        }

        try {
            $this->driveService = new Drive($client);
            return $this->driveService;
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Drive service', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Find a folder by name within a parent folder, or create it if it doesn't exist
     *
     * @param string $folderName Name of the folder to find or create
     * @param string $parentFolderId Parent folder ID
     * @return string|null The folder ID or null on failure
     */
    public function findOrCreateFolder(string $folderName, string $parentFolderId): ?string
    {
        $driveService = $this->getDriveService();
        if (!$driveService) {
            return null;
        }

        try {
            // Search for existing folder
            $query = sprintf(
                "name = '%s' and '%s' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                addslashes($folderName),
                $parentFolderId
            );

            $results = $driveService->files->listFiles([
                'q' => $query,
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
                'fields' => 'files(id, name)'
            ]);

            $files = $results->getFiles();
            if (count($files) > 0) {
                // Folder exists, return its ID
                return $files[0]->getId();
            }

            // Folder doesn't exist, create it
            $folderMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentFolderId]
            ]);

            $folder = $driveService->files->create($folderMetadata, [
                'supportsAllDrives' => true,
                'fields' => 'id'
            ]);

            Log::info('Created new folder in Google Drive', [
                'folder_name' => $folderName,
                'folder_id' => $folder->id,
                'parent_id' => $parentFolderId
            ]);

            return $folder->id;

        } catch (\Exception $e) {
            Log::error('Failed to find or create folder in Google Drive', [
                'folder_name' => $folderName,
                'parent_id' => $parentFolderId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get the Indonesian month name
     *
     * @param string $bulan Month number (01-12)
     * @return string The formatted month folder name (e.g., "01. Januari")
     */
    public function getMonthFolderName(string $bulan): string
    {
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $monthName = $months[$bulan] ?? 'Unknown';
        return $bulan . '. ' . $monthName;
    }

    /**
     * Get the target folder ID based on SKP type
     *
     * @param string $jenis SKP type (SKP Bulanan, SKP Tahunan (Penetapan), SKP Tahunan (Penilaian))
     * @param string $tahun Year
     * @param string|null $bulan Month (for SKP Bulanan)
     * @return string|null Target folder ID or null on failure
     */
    public function getSkpTargetFolder(string $jenis, string $tahun, ?string $bulan = null): ?string
    {
        $rootFolderId = config('google.drive.folder_id');
        if (empty($rootFolderId)) {
            Log::error('Google Drive folder ID not configured');
            return null;
        }

        try {
            if ($jenis === 'SKP Bulanan') {
                // Structure: {root}/{tahun}/{bulan. MonthName}
                $yearFolderId = $this->findOrCreateFolder($tahun, $rootFolderId);
                if (!$yearFolderId) {
                    return null;
                }

                $monthFolderName = $this->getMonthFolderName($bulan);
                return $this->findOrCreateFolder($monthFolderName, $yearFolderId);

            } elseif ($jenis === 'SKP Tahunan (Penetapan)') {
                // Structure: {root}/{tahun}/00. Penetapan
                $yearFolderId = $this->findOrCreateFolder($tahun, $rootFolderId);
                if (!$yearFolderId) {
                    return null;
                }

                return $this->findOrCreateFolder('00. Penetapan', $yearFolderId);

            } elseif ($jenis === 'SKP Tahunan (Penilaian)' || $jenis === 'SKP Evaluasi Tahunan') {
                // Structure: {root}/SKP Penilaian dan Evaluasi {tahun}
                $folderName = 'SKP Penilaian dan Evaluasi ' . $tahun;
                return $this->findOrCreateFolder($folderName, $rootFolderId);

            } else {
                // Unknown type, use root folder
                Log::warning('Unknown SKP type, using root folder', ['jenis' => $jenis]);
                return $rootFolderId;
            }

        } catch (\Exception $e) {
            Log::error('Failed to get target folder for SKP', [
                'jenis' => $jenis,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Upload a file to Google Drive
     *
     * @param string $filePath Full path to the local file
     * @param string $fileName Name for the file in Google Drive
     * @param string|null $folderId Optional folder ID (defaults to config value)
     * @return array{success: bool, file_id?: string, web_link?: string, error?: string}
     */
    public function uploadFile(string $filePath, string $fileName, ?string $folderId = null): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Google Drive integration is disabled'
            ];
        }

        if (!file_exists($filePath)) {
            Log::error('File not found for Google Drive upload', [
                'path' => $filePath
            ]);
            return [
                'success' => false,
                'error' => 'File not found: ' . $filePath
            ];
        }

        $driveService = $this->getDriveService();
        if (!$driveService) {
            return [
                'success' => false,
                'error' => 'Failed to initialize Google Drive service'
            ];
        }

        $folderId = $folderId ?? config('google.drive.folder_id');
        if (empty($folderId)) {
            Log::error('Google Drive folder ID not configured');
            return [
                'success' => false,
                'error' => 'Google Drive folder ID not configured'
            ];
        }

        try {
            // Check if file with same name already exists and delete it
            $this->deleteExistingFile($fileName, $folderId);

            // Create file metadata
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [$folderId]
            ]);

            // Get file content and mime type
            $content = file_get_contents($filePath);
            $mimeType = mime_content_type($filePath) ?: 'application/pdf';

            // Upload to Google Drive (supports both My Drive and Shared Drives)
            $file = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'supportsAllDrives' => true,
                'fields' => 'id, webViewLink, webContentLink'
            ]);

            Log::info('File uploaded to Google Drive successfully', [
                'file_id' => $file->id,
                'file_name' => $fileName,
                'folder_id' => $folderId
            ]);

            return [
                'success' => true,
                'file_id' => $file->id,
                'web_link' => $file->webViewLink ?? $file->webContentLink
            ];

        } catch (\Exception $e) {
            Log::error('Failed to upload file to Google Drive', [
                'error' => $e->getMessage(),
                'file' => $fileName,
                'folder_id' => $folderId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete existing file with same name in a folder (to prevent duplicates)
     *
     * @param string $fileName The file name to search for
     * @param string $folderId The folder ID to search in
     * @return void
     */
    protected function deleteExistingFile(string $fileName, string $folderId): void
    {
        $driveService = $this->getDriveService();
        if (!$driveService) {
            return;
        }

        try {
            // Search for existing file with same name in the folder
            $query = sprintf(
                "name = '%s' and '%s' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false",
                addslashes($fileName),
                $folderId
            );

            $results = $driveService->files->listFiles([
                'q' => $query,
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
                'fields' => 'files(id, name)'
            ]);

            $files = $results->getFiles();
            
            // Delete all matching files (usually just one)
            foreach ($files as $file) {
                $driveService->files->delete($file->getId(), ['supportsAllDrives' => true]);
                Log::info('Deleted existing file before upload', [
                    'file_name' => $fileName,
                    'file_id' => $file->getId(),
                    'folder_id' => $folderId
                ]);
            }

        } catch (\Exception $e) {
            // Log but don't fail - we'll just upload a new file
            Log::warning('Failed to delete existing file', [
                'file_name' => $fileName,
                'folder_id' => $folderId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a file from Google Drive
     *
     * @param string $fileId Google Drive file ID
     * @return array{success: bool, error?: string}
     */
    public function deleteFile(string $fileId): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Google Drive integration is disabled'
            ];
        }

        $driveService = $this->getDriveService();
        if (!$driveService) {
            return [
                'success' => false,
                'error' => 'Failed to initialize Google Drive service'
            ];
        }

        try {
            $driveService->files->delete($fileId, ['supportsAllDrives' => true]);

            Log::info('File deleted from Google Drive', [
                'file_id' => $fileId
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Failed to delete file from Google Drive', [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test the Google Drive connection
     *
     * @return array{success: bool, message?: string, error?: string}
     */
    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Google Drive integration is disabled'
            ];
        }

        $driveService = $this->getDriveService();
        if (!$driveService) {
            return [
                'success' => false,
                'error' => 'Failed to initialize Google Drive service'
            ];
        }

        try {
            // Try to get info about the configured folder
            $folderId = config('google.drive.folder_id');
            if (empty($folderId)) {
                return [
                    'success' => false,
                    'error' => 'Google Drive folder ID not configured'
                ];
            }

            $folder = $driveService->files->get($folderId, [
                'fields' => 'id, name, driveId',
                'supportsAllDrives' => true
            ]);

            return [
                'success' => true,
                'message' => 'Successfully connected to Google Drive. Folder: ' . $folder->name
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
}
