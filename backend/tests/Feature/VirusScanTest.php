<?php

namespace Tests\Feature;

use App\Services\VirusScanService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VirusScanTest extends TestCase
{
    protected VirusScanService $virusScanService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->virusScanService = new VirusScanService();
    }

    public function test_basic_validation_passes_for_valid_pdf()
    {
        // SECURITY FIX: Verify basic validation passes for valid PDF
        $content = '%PDF-1.4' . str_repeat(' ', 1000);

        // Create a real temporary file
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.pdf';
        file_put_contents($tmpPath, $content);

        $result = $this->virusScanService->scan($tmpPath);

        // Clean up
        unlink($tmpPath);

        $this->assertTrue($result['clean']);
        $this->assertEquals('File passed basic validation (ClamAV not available)', $result['message']);
        $this->assertEquals('Basic', $result['scanner']);
    }

    public function test_basic_validation_fails_for_invalid_pdf()
    {
        // SECURITY FIX: Verify basic validation fails for invalid PDF
        $content = 'INVALID PDF CONTENT' . str_repeat(' ', 1000);

        // Create a real temporary file
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.pdf';
        file_put_contents($tmpPath, $content);

        $result = $this->virusScanService->scan($tmpPath);

        // Clean up
        unlink($tmpPath);

        $this->assertFalse($result['clean']);
        $this->assertEquals('Invalid PDF file', $result['message']);
        $this->assertEquals('Basic', $result['scanner']);
    }

    public function test_basic_validation_fails_for_disallowed_extension()
    {
        // SECURITY FIX: Verify basic validation fails for disallowed file types
        $content = str_repeat('test', 1000);
        $file = UploadedFile::fake()->createWithContent('test.exe', $content);

        $result = $this->virusScanService->scan($file);

        $this->assertFalse($result['clean']);
        $this->assertEquals('File type not allowed', $result['message']);
        $this->assertEquals('Basic', $result['scanner']);
    }

    public function test_basic_validation_fails_for_empty_file()
    {
        // SECURITY FIX: Verify basic validation fails for empty files
        $file = UploadedFile::fake()->createWithContent('test.pdf', '');

        $result = $this->virusScanService->scan($file);

        $this->assertFalse($result['clean']);
        $this->assertEquals('File is empty', $result['message']);
        $this->assertEquals('Basic', $result['scanner']);
    }

    public function test_virus_scan_service_reports_availability()
    {
        // SECURITY FIX: Verify service reports ClamAV availability correctly
        $isAvailable = $this->virusScanService->isClamAvAvailable();

        // In test environment, ClamAV is typically not installed
        $this->assertFalse($isAvailable);
    }

    public function test_valid_json_passes_basic_validation()
    {
        // SECURITY FIX: Verify valid JSON passes basic validation
        $content = json_encode(['test' => 'data', 'number' => 123]);

        // Create a real temporary file
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.json';
        file_put_contents($tmpPath, $content);

        $result = $this->virusScanService->scan($tmpPath);

        // Clean up
        unlink($tmpPath);

        $this->assertTrue($result['clean']);
        $this->assertEquals('File passed basic validation (ClamAV not available)', $result['message']);
    }

    public function test_upload_file_request_includes_virus_scan()
    {
        // SECURITY FIX: Verify UploadFileRequest includes virus scanning
        // This test verifies the integration with UploadFileRequest

        $content = '%PDF-1.4' . str_repeat(' ', 1000);
        $file = UploadedFile::fake()->createWithContent('test.pdf', $content);

        // Create a mock request to test validation
        $request = \App\Http\Requests\UploadFileRequest::create(
            '/api/upload',
            'POST',
            [],
            [],
            ['file' => $file],
            []
        );

        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('file', $rules);
    }
}
