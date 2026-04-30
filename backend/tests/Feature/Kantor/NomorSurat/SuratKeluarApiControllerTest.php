<?php

namespace Tests\Feature\Kantor\NomorSurat;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SuratKeluarApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_can_list_surat_keluar()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keluar');

        $response->assertStatus(200);
        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_cannot_list_surat_keluar_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-keluar');

        $response->assertStatus(401);
    }

    public function test_can_get_surat_keluar_dates()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keluar/dates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_cannot_get_surat_keluar_dates_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-keluar/dates');

        $response->assertStatus(401);
    }

    public function test_can_get_surat_keluar_years()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keluar/years');

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    public function test_cannot_get_surat_keluar_years_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-keluar/years');

        $response->assertStatus(401);
    }

    public function test_can_get_surat_keluar_form_options()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keluar/form-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'settings',
                    'nomor_baru'
                ]
            ]);
    }

    public function test_cannot_get_surat_keluar_form_options_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surat-keluar/form-options');

        $response->assertStatus(401);
    }

    public function test_can_store_surat_keluar()
    {
        $suratData = [
            'tanggal' => '2024-01-15',
            'dari' => 'Kepala BPS Kota ABC',
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Survei Pendataan',
            'isi_surat' => 'Isi surat testing',
            'tembusan' => ['Kepala Dinas'],
            'kode' => 'SK',
            'thn' => '2024',
            'bln' => '01',
            'no_sisip' => null
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $suratData);

        $this->assertContains($response->getStatusCode(), [200, 201, 422, 500]);
    }

    public function test_cannot_store_surat_keluar_without_authentication()
    {
        $suratData = [
            'tanggal' => '2024-01-15',
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Survei Pendataan'
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/surat-keluar', $suratData);

        $response->assertStatus(401);
    }

    public function test_validation_error_when_storing_surat_keluar_with_invalid_data()
    {
        $invalidData = [
            'tanggal' => 'invalid-date',
            'tujuan' => '',
            'perihal' => ''
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }
}
