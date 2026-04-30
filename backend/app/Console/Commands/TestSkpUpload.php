<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class TestSkpUpload extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'google:test-skp-upload 
                            {--tahun=2026 : Year for the test}
                            {--bulan=05 : Month for SKP Bulanan test}';

    /**
     * The console command description.
     */
    protected $description = 'Test SKP upload to Google Drive with all 4 SKP types';

    /**
     * Execute the console command.
     */
    public function handle(GoogleDriveService $googleDriveService): int
    {
        $tahun = $this->option('tahun');
        $bulan = $this->option('bulan');

        $this->info("Testing SKP upload with tahun={$tahun}, bulan={$bulan}");
        $this->newLine();

        // Check if enabled
        if (!$googleDriveService->isEnabled()) {
            $this->error('Google Drive integration is disabled. Set GOOGLE_DRIVE_ENABLED=true');
            return Command::FAILURE;
        }

        // Create a simple test PDF
        $testPdfPath = storage_path('app/test-skp.pdf');
        $this->createTestPdf($testPdfPath);
        $this->info("Created test PDF: {$testPdfPath}");
        $this->newLine();

        // Test all 4 SKP types
        $skpTypes = [
            [
                'jenis' => 'SKP Bulanan',
                'tahun' => $tahun,
                'bulan' => $bulan,
                'expected_path' => "{$tahun}/" . $googleDriveService->getMonthFolderName($bulan)
            ],
            [
                'jenis' => 'SKP Tahunan (Penetapan)',
                'tahun' => $tahun,
                'bulan' => null,
                'expected_path' => "{$tahun}/00. Penetapan"
            ],
            [
                'jenis' => 'SKP Tahunan (Penilaian)',
                'tahun' => $tahun,
                'bulan' => null,
                'expected_path' => "SKP Penilaian dan Evaluasi {$tahun}"
            ],
            [
                'jenis' => 'SKP Evaluasi Tahunan',
                'tahun' => $tahun,
                'bulan' => null,
                'expected_path' => "SKP Penilaian dan Evaluasi {$tahun}"
            ],
        ];

        $allSuccess = true;

        foreach ($skpTypes as $index => $skpType) {
            $this->line("─────────────────────────────────────────");
            $this->info("Test " . ($index + 1) . ": " . $skpType['jenis']);
            $this->line("Expected folder: " . $skpType['expected_path']);

            // Get target folder
            $folderId = $googleDriveService->getSkpTargetFolder(
                $skpType['jenis'],
                $skpType['tahun'],
                $skpType['bulan']
            );

            if (!$folderId) {
                $this->error("✗ Failed to get/create target folder");
                $allSuccess = false;
                continue;
            }

            $this->info("✓ Target folder ID: {$folderId}");

            // Upload test file
            $fileName = "TEST_{$skpType['jenis']}_{$tahun}" . ($skpType['bulan'] ? "_{$skpType['bulan']}" : "") . ".pdf";
            $fileName = str_replace(['(', ')', ' '], ['', '', '_'], $fileName);
            
            $result = $googleDriveService->uploadFile($testPdfPath, $fileName, $folderId);

            if ($result['success']) {
                $this->info("✓ Uploaded: {$fileName}");
                $this->info("  File ID: " . ($result['file_id'] ?? 'N/A'));
            } else {
                $this->error("✗ Upload failed: " . ($result['error'] ?? 'Unknown error'));
                $allSuccess = false;
            }

            $this->newLine();
        }

        // Cleanup test PDF
        if (file_exists($testPdfPath)) {
            unlink($testPdfPath);
            $this->line("Cleaned up test PDF");
        }

        $this->newLine();
        $this->line("─────────────────────────────────────────");

        if ($allSuccess) {
            $this->info("🎉 All tests passed! Check your Google Drive folder.");
            return Command::SUCCESS;
        } else {
            $this->warn("⚠ Some tests failed. Check the errors above.");
            return Command::FAILURE;
        }
    }

    /**
     * Create a simple test PDF file
     */
    private function createTestPdf(string $path): void
    {
        // Create a minimal valid PDF
        $pdf = "%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>
endobj
4 0 obj
<< /Length 44 >>
stream
BT /F1 12 Tf 100 700 Td (Test SKP PDF) Tj ET
endstream
endobj
xref
0 5
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000206 00000 n 
trailer
<< /Size 5 /Root 1 0 R >>
startxref
300
%%EOF";

        file_put_contents($path, $pdf);
    }
}
