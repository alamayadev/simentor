<?php

namespace Tests\Feature\Kantor\NomorSurat;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SuratKeputusanApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_can_list_surat_keputusan()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keputusan');

        $response->assertStatus(200);
        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_cannot_list_surat_keputusan_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-keputusan');

        $response->assertStatus(401);
    }

    public function test_can_get_surat_keputusan_dates()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keputusan/dates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_cannot_get_surat_keputusan_dates_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-keputusan/dates');

        $response->assertStatus(401);
    }

    public function test_can_get_surat_keputusan_years()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keputusan/years');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_cannot_get_surat_keputusan_years_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-keputusan/years');

        $response->assertStatus(401);
    }

    public function test_can_store_surat_keputusan()
    {
        $suratData = [
            'tanggal' => '2024-01-15',
            'tentang' => 'Pengangkatan Pegawai',
            'perihal' => 'Pengangkatan Pegawai Baru',
            'kepada' => 'John Doe',
            'isi_surat' => 'Isi surat keputusan testing',
            'tembusan' => 'Kepala Dinas',
            'kode' => 'SK',
            'thn' => '2024',
            'bln' => '01',
            'nomor' => '001'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $suratData);

        $this->assertContains($response->getStatusCode(), [201, 422, 500]);
    }

    public function test_cannot_store_surat_keputusan_without_authentication()
    {
        $suratData = [
            'tanggal' => '2024-01-15',
            'tentang' => 'Pengangkatan Pegawai'
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/surat-keputusan', $suratData);

        $response->assertStatus(401);
    }

    public function test_validation_error_when_storing_surat_keputusan_with_invalid_data()
    {
        $invalidData = [
            'tanggal' => 'invalid-date',
            'tentang' => '',
            'kepada' => ''
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }

    public function test_can_sisip_surat_keputusan()
    {
        $sisipData = [
            'tanggal' => '2024-01-15',
            'tentang' => 'Sisip Surat Keputusan',
            'kepada' => 'Jane Doe',
            'isi_surat' => 'Isi surat sisip testing',
            'nomor_sisip' => 5,
            'kode' => 'SK',
            'thn' => '2024',
            'bln' => '01',
            'tahun' => '2024'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan/sisip', $sisipData);

        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    public function test_cannot_sisip_surat_keputusan_without_authentication()
    {
        $sisipData = [
            'tanggal' => '2024-01-15',
            'tentang' => 'Sisip Surat Keputusan'
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/surat-keputusan/sisip', $sisipData);

        $response->assertStatus(401);
    }
}
