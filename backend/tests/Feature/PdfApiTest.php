<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Penugasan;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Setting;
use Laravel\Sanctum\Sanctum;

class PdfApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $penugasan;
    protected $kegiatan;
    protected $mitra;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required settings for PDF generation
        Setting::create([
            'tahun' => '2025',
            'key' => 'PPK',
            'value' => 'Test PPK',
            'grup' => 1,
        ]);

        Setting::create([
            'tahun' => '2025',
            'key' => 'NIP_PPK',
            'value' => '123456789',
            'grup' => 1,
        ]);

        Setting::create([
            'tahun' => '2025',
            'key' => 'FORMAT_NO_SPK',
            'value' => '/SPK/TEST/',
            'grup' => 1,
        ]);

        Setting::create([
            'tahun' => '2025',
            'key' => 'FORMAT_NO_BAST',
            'value' => '/BAST/TEST/',
            'grup' => 1,
        ]);

        Setting::create([
            'tahun' => '2025',
            'key' => 'TAHUN_SPK',
            'value' => '2025',
            'grup' => 1,
        ]);

        Setting::create([
            'tahun' => '2025',
            'key' => 'TAHUN_BAST',
            'value' => '2025',
            'grup' => 1,
        ]);

        Setting::create([
            'tahun' => '2025',
            'key' => 'KODE_RING',
            'value' => 'TEST',
            'grup' => 1,
        ]);

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a test kegiatan
        $this->kegiatan = Kegiatan::create([
            'tahun' => '2025',
            'fungsi' => 'Produksi',
            'kode_kelompok_kegiatan' => '01',
            'kode_kegiatan' => '01.01',
            'nama' => 'Test Kegiatan',
            'tgl_mulai' => '2025-01-01',
            'tgl_selesai' => '2025-12-31',
            'jenis_kegiatan' => 'PENGUMPULAN DATA',
            'rate_pcl' => 50000,
            'rate_pml' => 75000,
            'rate_entri' => 10000,
        ]);

        // Create a test mitra using forceFill to bypass mass assignment protection
        $this->mitra = new Mitra();
        $this->mitra->forceFill([
            'email' => 'mitra@example.com',
            'sobat_id' => '123456',
            'posisi' => 'PCL',
            'status_seleksi' => 'Lulus',
            'posisi_daftar' => 'PCL',
            'nama_lengkap' => 'Test Mitra',
            'alamat_detail' => 'Test Address',
            'alamat_prov' => 'Test Province',
            'alamat_kab' => 'Test City',
            'alamat_kec' => 'Test District',
            'alamat_desa' => 'Test Village',
            'tgl_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_kawin' => 'Kawin',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Swasta',
            'no_telp' => '081234567890',
            'nik' => '1234567890123456',
        ]);
        $this->mitra->save();

        // Create a test penugasan with all required fields
        $this->penugasan = Penugasan::create([
            'kegiatan_id' => $this->kegiatan->id,
            'jabatan_tugas' => 'PML',
            'mitra_id' => $this->mitra->id,
            'volume' => 1,
            'nilai' => 3930000,
            'bln_bayar' => '2025-06-01',
            'created_by' => $this->user->id,
            'no_bast' => '001',
            'tgl_bast' => '2025-12-14',
            'no_sk' => '001',
            'tgl_sk' => '2025-09-14',
            'jangka_waktu_mulai' => '2025-09-14',
            'jangka_waktu_selesai' => '2025-12-14',
        ]);
    }

    /**
     * Test SPK PDF generation endpoint.
     *
     * @return void
     */
    public function test_can_generate_spk_pdf()
    {
        // Authenticate the user
        Sanctum::actingAs($this->user);

        // Make request to SPK PDF endpoint
        $response = $this->getJson("/api/pdf/spk/{$this->penugasan->id}");

        // Assert response status
        $response->assertStatus(200);

        // Assert response is a PDF
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * Test BAST PDF generation endpoint.
     *
     * @return void
     */
    public function test_can_generate_bast_pdf()
    {
        // Authenticate the user
        Sanctum::actingAs($this->user);

        // Make request to BAST PDF endpoint
        $response = $this->getJson("/api/pdf/bast/{$this->penugasan->id}");

        // Assert response status
        $response->assertStatus(200);

        // Assert response is a PDF
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * Test SPK PDF generation with unauthenticated user.
     *
     * @return void
     */
    public function test_cannot_generate_spk_pdf_when_unauthenticated()
    {
        // Make request to SPK PDF endpoint without authentication
        $response = $this->getJson("/api/pdf/spk/{$this->penugasan->id}");

        // Assert response status
        $response->assertStatus(401);
    }

    /**
     * Test BAST PDF generation with unauthenticated user.
     *
     * @return void
     */
    public function test_cannot_generate_bast_pdf_when_unauthenticated()
    {
        // Make request to BAST PDF endpoint without authentication
        $response = $this->getJson("/api/pdf/bast/{$this->penugasan->id}");

        // Assert response status
        $response->assertStatus(401);
    }

    /**
     * Test SPK PDF generation with non-existent penugasan.
     *
     * @return void
     */
    public function test_cannot_generate_spk_pdf_with_non_existent_penugasan()
    {
        // Authenticate the user
        Sanctum::actingAs($this->user);

        // Make request to SPK PDF endpoint with non-existent penugasan ID
        $response = $this->getJson("/api/pdf/spk/999999");

        // Assert response status
        $response->assertStatus(404);
    }

    /**
     * Test BAST PDF generation with non-existent penugasan.
     *
     * @return void
     */
    public function test_cannot_generate_bast_pdf_with_non_existent_penugasan()
    {
        // Authenticate the user
        Sanctum::actingAs($this->user);

        // Make request to BAST PDF endpoint with non-existent penugasan ID
        $response = $this->getJson("/api/pdf/bast/999999");

        // Assert response status
        $response->assertStatus(404);
    }

    /**
     * Test SPK PDF generation with penugasan missing required fields.
     *
     * @return void
     */
    public function test_cannot_generate_spk_pdf_with_missing_required_fields()
    {
        // Create a penugasan without required fields
        $incompletePenugasan = Penugasan::create([
            'kegiatan_id' => $this->kegiatan->id,
            'jabatan_tugas' => 'PML',
            'mitra_id' => $this->mitra->id,
            'volume' => 1,
            'nilai' => 3930000,
            'bln_bayar' => '2025-06-01',
            'created_by' => $this->user->id,
            // Missing required fields for SPK: no_sk, tgl_sk, jangka_waktu_mulai, jangka_waktu_selesai
        ]);

        // Authenticate the user
        Sanctum::actingAs($this->user);

        // Make request to SPK PDF endpoint with incomplete penugasan
        $response = $this->getJson("/api/pdf/spk/{$incompletePenugasan->id}");

        // Assert response status
        $response->assertStatus(422);
    }

    /**
     * Test BAST PDF generation with penugasan missing required fields.
     *
     * @return void
     */
    public function test_cannot_generate_bast_pdf_with_missing_required_fields()
    {
        // Create a penugasan without required fields
        $incompletePenugasan = Penugasan::create([
            'kegiatan_id' => $this->kegiatan->id,
            'jabatan_tugas' => 'PML',
            'mitra_id' => $this->mitra->id,
            'volume' => 1,
            'nilai' => 3930000,
            'bln_bayar' => '2025-06-01',
            'created_by' => $this->user->id,
            // Missing required fields for BAST: no_bast, tgl_bast
        ]);

        // Authenticate the user
        Sanctum::actingAs($this->user);

        // Make request to BAST PDF endpoint with incomplete penugasan
        $response = $this->getJson("/api/pdf/bast/{$incompletePenugasan->id}");

        // Assert response status
        $response->assertStatus(422);
    }
}
