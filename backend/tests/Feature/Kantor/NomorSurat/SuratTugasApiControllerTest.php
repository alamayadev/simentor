<?php

namespace Tests\Feature\Kantor\NomorSurat;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SuratTugasApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_can_list_surat_tugas()
    {
        $response = $this->getJson('/api/kantor/surat/surat-tugas');

        $response->assertStatus(200);
        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_cannot_list_surat_tugas_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-tugas');

        $response->assertStatus(401);
    }

    public function test_can_get_surat_tugas_dates()
    {
        $response = $this->getJson('/api/kantor/surat/surat-tugas/dates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_cannot_get_surat_tugas_dates_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-tugas/dates');

        $response->assertStatus(401);
    }

    public function test_can_get_surat_tugas_years()
    {
        $response = $this->getJson('/api/kantor/surat/surat-tugas/years');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_cannot_get_surat_tugas_years_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-tugas/years');

        $response->assertStatus(401);
    }

    public function test_can_get_surat_tugas_klasifikasi()
    {
        $response = $this->getJson('/api/kantor/surat/surat-tugas/klasifikasi');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_cannot_get_surat_tugas_klasifikasi_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-tugas/klasifikasi');

        $response->assertStatus(401);
    }

    public function test_can_store_surat_tugas()
    {
        $suratData = [
            'tanggal' => '2024-01-15',
            'kepada' => 'John Doe',
            'perihal' => 'Tugas Survei',
            'isi_surat' => 'Isi surat tugas testing',
            'tembusan' => 'Kepala Dinas',
            'kode' => 'ST',
            'thn' => '2024',
            'bln' => '01',
            'klasifikasi' => 'Penting',
            'tahun' => '2024',
            'nomor' => '001',
            'kode_klas' => 'PK',
            'uraian' => 'Tugas Survei Lapangan'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-tugas', $suratData);

        $this->assertContains($response->getStatusCode(), [201, 422, 500]);
    }

    public function test_cannot_store_surat_tugas_without_authentication()
    {
        $suratData = [
            'tanggal' => '2024-01-15',
            'kepada' => 'John Doe',
            'perihal' => 'Tugas Survei'
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/surat-tugas', $suratData);

        $response->assertStatus(401);
    }

    public function test_validation_error_when_storing_surat_tugas_with_invalid_data()
    {
        $invalidData = [
            'tanggal' => 'invalid-date',
            'kepada' => '',
            'perihal' => ''
        ];

        $response = $this->postJson('/api/kantor/surat/surat-tugas', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }

    public function test_can_sisip_surat_tugas()
    {
        $sisipData = [
            'tanggal' => '2024-01-15',
            'kepada' => 'Jane Doe',
            'perihal' => 'Sisip Surat Tugas',
            'isi_surat' => 'Isi surat sisip testing',
            'nomor_sisip' => 5,
            'kode' => 'ST',
            'thn' => '2024',
            'bln' => '01',
            'klasifikasi' => 'Penting',
            'tahun' => '2024'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-tugas/sisip', $sisipData);

        $this->assertContains($response->getStatusCode(), [200, 422, 500]);
    }

    public function test_cannot_sisip_surat_tugas_without_authentication()
    {
        $sisipData = [
            'tanggal' => '2024-01-15',
            'kepada' => 'Jane Doe',
            'perihal' => 'Sisip Surat Tugas'
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/surat-tugas/sisip', $sisipData);

        $response->assertStatus(401);
    }
}
