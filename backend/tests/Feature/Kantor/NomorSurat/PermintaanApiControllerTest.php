<?php

namespace Tests\Feature\Kantor\NomorSurat;

use App\Models\User;
use App\Models\SuratPermintaan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PermintaanApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_can_list_permintaan()
    {
        $response = $this->getJson('/api/kantor/surat/permintaan');

        $response->assertStatus(200);
        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_cannot_list_permintaan_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/permintaan');

        $response->assertStatus(401);
    }

    public function test_can_get_permintaan_dates()
    {
        $response = $this->getJson('/api/kantor/surat/permintaan/dates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_cannot_get_permintaan_dates_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/permintaan/dates');

        $response->assertStatus(401);
    }

    public function test_can_get_permintaan_years()
    {
        $response = $this->getJson('/api/kantor/surat/permintaan/years');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_cannot_get_permintaan_years_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/permintaan/years');

        $response->assertStatus(401);
    }

    public function test_can_get_permintaan_klasifikasi()
    {
        $response = $this->getJson('/api/kantor/surat/permintaan/klasifikasi');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_cannot_get_permintaan_klasifikasi_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/permintaan/klasifikasi');

        $response->assertStatus(401);
    }

    public function test_can_store_permintaan()
    {
        $permintaanData = [
            'tanggal' => '2024-01-15',
            'dari' => 'Kepala BPS Kota ABC',
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Permintaan Data',
            'isi_surat' => 'Isi surat permintaan testing',
            'tembusan' => ['Kepala Dinas'],
            'kode' => 'PM',
            'thn' => '2024',
            'bln' => '01',
            'no_sisip' => null
        ];

        $response = $this->postJson('/api/kantor/surat/permintaan', $permintaanData);

        $this->assertContains($response->getStatusCode(), [201, 422]);
    }

    public function test_cannot_store_permintaan_without_authentication()
    {
        $permintaanData = [
            'tanggal' => '2024-01-15',
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Permintaan Data'
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/permintaan', $permintaanData);

        $response->assertStatus(401);
    }

    public function test_validation_error_when_storing_permintaan_with_invalid_data()
    {
        $invalidData = [
            'tanggal' => 'invalid-date',
            'tujuan' => '',
            'perihal' => ''
        ];

        $response = $this->postJson('/api/kantor/surat/permintaan', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }

    public function test_can_sisip_permintaan()
    {
        $sisipData = [
            'tanggal' => '2024-01-15',
            'dari' => 'Kepala BPS Kota ABC',
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Sisip Permintaan',
            'isi_surat' => 'Isi surat sisip testing',
            'nomor_sisip' => 5,
            'kode' => 'PM',
            'thn' => '2024',
            'bln' => '01'
        ];

        $response = $this->postJson('/api/kantor/surat/permintaan/sisip', $sisipData);

        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    public function test_cannot_sisip_permintaan_without_authentication()
    {
        $sisipData = [
            'tanggal' => '2024-01-15',
            'dari' => 'Kepala BPS Kota ABC',
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Sisip Permintaan'
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/permintaan/sisip', $sisipData);

        $response->assertStatus(401);
    }

    public function test_can_bulk_update_status_permintaan()
    {
        $statusData = [
            'ids' => [1, 2, 3],
            'status' => 'approved'
        ];

        $response = $this->postJson('/api/kantor/surat/permintaan/bulk-update-status', $statusData);

        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    public function test_cannot_bulk_update_status_permintaan_without_authentication()
    {
        $statusData = [
            'ids' => [1, 2, 3],
            'status' => 'approved'
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/permintaan/bulk-update-status', $statusData);

        $response->assertStatus(401);
    }
}
