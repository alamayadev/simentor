<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\LaporanPerjalananDinas;
use App\Models\LaporanPerjalananDinasDetail;
use App\Models\LaporanPerjalananDinasDokumentasi;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Storage;

class LaperdinPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create user
        $this->user = User::factory()->create();
    }

    /**
     * Test generating Laporan Perjalanan Dinas PDF
     *
     * @return void
     */
    public function test_can_generate_laperdin_pdf()
    {
        Sanctum::actingAs($this->user);

        // Create Laporan
        $laporan = LaporanPerjalananDinas::factory()->create([
            'nama_traveler' => 'Budi Santoso',
            'tujuan' => 'Bandung',
            'lama_tanggal' => '2 Hari (15-16 Januari 2024)',
            'dalam_rangka' => 'Monitoring',
            'pembebanan' => 'DIPA 2024',
        ]);

        // Create Details
        LaporanPerjalananDinasDetail::factory()->count(2)->create([
            'laporan_perjalanan_dinas_id' => $laporan->id,
        ]);

        // Create Dokumentasi (5 images to trigger grid layout)
        LaporanPerjalananDinasDokumentasi::factory()->count(5)->create([
            'laporan_perjalanan_dinas_id' => $laporan->id,
        ]);

        // Call API
        $response = $this->get("/api/kantor/laporan-perjalanan-dinas/{$laporan->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Save output to public/laperdin_v6.pdf
        $content = $response->getContent();
        $filename = 'laperdin_test_output_' . time() . '.pdf';
        $outputPath = public_path($filename);
        file_put_contents($outputPath, $content);

        $this->assertFileExists($outputPath);
        
        // Output path for user info
        echo "\nPDF generated at: " . $outputPath . "\n";
    }
}
