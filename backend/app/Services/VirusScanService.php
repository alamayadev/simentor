<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

/**
 * Virus Scan Service
 *
 * Provides virus scanning functionality for uploaded files.
 * Supports ClamAV scanner when available, with fallback to basic validation.
 */
class VirusScanService
{
    protected ?string $clamScanPath;
    protected bool $clamAvAvailable;

    public function __construct()
    {
        $this->clamScanPath = config('services.clamav.path', '/usr/bin/clamscan');
        $this->clamAvAvailable = $this->checkClamAvAvailable();
    }

    /**
     * Scan a file for viruses
     *
     * @param \Illuminate\Http\UploadedFile|string $file File path or UploadedFile instance
     * @return array{clean: bool, message: string, scanner: string}
     */
    public function scan($file): array
    {
        $filePath = $file instanceof UploadedFile ? $file->getPathname() : $file;

        // If ClamAV is available, use it
        if ($this->clamAvAvailable) {
            return $this->scanWithClamAv($filePath);
        }

        // Fall back to basic validation
        return $this->basicValidation($filePath);
    }

    /**
     * Check if ClamAV scanner is available
     */
    protected function checkClamAvAvailable(): bool
    {
        if (!file_exists($this->clamScanPath)) {
            Log::warning('ClamAV scanner not found at: ' . $this->clamScanPath);
            return false;
        }

        if (!is_executable($this->clamScanPath)) {
            Log::warning('ClamAV scanner is not executable: ' . $this->clamScanPath);
            return false;
        }

        return true;
    }

    /**
     * Scan file using ClamAV
     *
     * @param string $filePath
     * @return array{clean: bool, message: string, scanner: string}
     */
    protected function scanWithClamAv(string $filePath): array
    {
        try {
            // Run clamscan command
            $command = escapeshellcmd($this->clamScanPath) . ' --no-summary ' . escapeshellarg($filePath);
            $output = [];
            $returnCode = 0;

            exec($command . ' 2>&1', $output, $returnCode);

            // ClamAV returns 0 if no virus found, 1 if virus found
            if ($returnCode === 0) {
                Log::info('File scanned with ClamAV: clean', ['file' => $filePath]);
                return [
                    'clean' => true,
                    'message' => 'File is clean',
                    'scanner' => 'ClamAV',
                ];
            } elseif ($returnCode === 1) {
                Log::warning('Virus detected by ClamAV', [
                    'file' => $filePath,
                    'output' => implode("\n", $output),
                ]);
                return [
                    'clean' => false,
                    'message' => 'File contains virus or malware',
                    'scanner' => 'ClamAV',
                ];
            } else {
                Log::error('ClamAV scan failed', [
                    'file' => $filePath,
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output),
                ]);
                // Fall back to basic validation on error
                return $this->basicValidation($filePath);
            }
        } catch (\Exception $e) {
            Log::error('ClamAV scan exception', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            // Fall back to basic validation on exception
            return $this->basicValidation($filePath);
        }
    }

    /**
     * Basic file validation (fallback when ClamAV is not available)
     *
     * SECURITY NOTE: This is a basic fallback and does NOT replace proper virus scanning.
     * It only checks file size, extension, and basic structure.
     * For production, you should install and configure ClamAV.
     *
     * @param string $filePath
     * @return array{clean: bool, message: string, scanner: string}
     */
    protected function basicValidation(string $filePath): array
    {
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                return [
                    'clean' => false,
                    'message' => 'File does not exist',
                    'scanner' => 'Basic',
                ];
            }

            // Check file size (max 10MB)
            $fileSize = filesize($filePath);
            if ($fileSize > 10 * 1024 * 1024) {
                return [
                    'clean' => false,
                    'message' => 'File too large',
                    'scanner' => 'Basic',
                ];
            }

            // Check if file is empty
            if ($fileSize === 0) {
                return [
                    'clean' => false,
                    'message' => 'File is empty',
                    'scanner' => 'Basic',
                ];
            }

            // Get file extension
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            // Check allowed extensions
            $allowedExtensions = ['pdf', 'json', 'png', 'jpg', 'jpeg', 'gif', 'doc', 'docx', 'xls', 'xlsx'];
            if (!in_array($extension, $allowedExtensions)) {
                return [
                    'clean' => false,
                    'message' => 'File type not allowed',
                    'scanner' => 'Basic',
                ];
            }

            // Verify file signature for specific types
            if ($extension === 'pdf') {
                $handle = fopen($filePath, 'rb');
                $magic = fread($handle, 4);
                fclose($handle);

                if (substr($magic, 0, 4) !== '%PDF') {
                    return [
                        'clean' => false,
                        'message' => 'Invalid PDF file',
                        'scanner' => 'Basic',
                    ];
                }
            }

            Log::info('File passed basic validation', ['file' => $filePath]);

            return [
                'clean' => true,
                'message' => 'File passed basic validation (ClamAV not available)',
                'scanner' => 'Basic',
            ];
        } catch (\Exception $e) {
            Log::error('Basic validation failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            return [
                'clean' => false,
                'message' => 'File validation failed',
                'scanner' => 'Basic',
            ];
        }
    }

    /**
     * Check if ClamAV is available
     */
    public function isClamAvAvailable(): bool
    {
        return $this->clamAvAvailable;
    }
}
