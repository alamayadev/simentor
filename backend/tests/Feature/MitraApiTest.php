<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\Penugasan;

class MitraApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    public function test_can_list_mitra()
    {
        // Create some mitra records
        Mitra::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/mitra');

        $response->assertStatus(200);
        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_can_filter_mitra_by_name()
    {
        // Create mitra records
        Mitra::factory()->create(['nama_lengkap' => 'Budi Santoso']);
        Mitra::factory()->create(['nama_lengkap' => 'Andi Saputra']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/mitra?filter[nama_lengkap]=Budi');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_lengkap', 'Budi Santoso');
    }

    public function test_can_get_mitra_filters()
    {
        // Create mitra records with different kecamatan, desa, and posisi
        Mitra::factory()->create([
            'keca' => 'Menteng',
            'desa' => 'Kebon Sirih',
            'posisi' => 'PCL'
        ]);

        Mitra::factory()->create([
            'keca' => 'Menteng',
            'desa' => 'Gambir',
            'posisi' => 'PML'
        ]);

        Mitra::factory()->create([
            'keca' => 'Gambir',
            'desa' => 'Kwitang',
            'posisi' => 'PCL'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/mitra/filters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'kecList',
                    'desaList',
                    'posisiList'
                ]
            ])
            ->assertJsonCount(2, 'data.kecList')
            ->assertJsonCount(0, 'data.desaList')
            ->assertJsonCount(2, 'data.posisiList');
    }

    public function test_can_get_mitra_filters_with_selected_keca()
    {
        // Create mitra records with different kecamatan and desa
        Mitra::factory()->create([
            'keca' => 'Menteng',
            'desa' => 'Kebon Sirih'
        ]);

        Mitra::factory()->create([
            'keca' => 'Menteng',
            'desa' => 'Gambir'
        ]);

        Mitra::factory()->create([
            'keca' => 'Gambir',
            'desa' => 'Kwitang'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/mitra/filters?selected_keca=Menteng');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'kecList',
                    'desaList',
                    'posisiList'
                ]
            ])
            ->assertJsonCount(2, 'data.kecList')
            ->assertJsonCount(2, 'data.desaList') // Only desa from Menteng kecamatan
            ->assertJsonFragment(['desaList' => ['Gambir', 'Kebon Sirih']]);
    }

    public function test_can_get_penugasan_options()
    {
        // Create some kegiatan records for current year
        $currentYear = date('Y');
        Kegiatan::factory()->create(['nama' => 'Sensus Penduduk ' . $currentYear, 'tahun' => $currentYear]);
        Kegiatan::factory()->create(['nama' => 'Survei Harga Konsumen ' . $currentYear, 'tahun' => $currentYear]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/mitra/penugasan-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'jabatanTugasOptions',
                    'kegiatanOptions' => [
                        '*' => [
                            'id',
                            'nama'
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(4, 'data.jabatanTugasOptions') // PCL, PML, OPERATOR, SUPERVISOR (from JabatanTugasType enum)
            ->assertJsonCount(2, 'data.kegiatanOptions');
    }

    public function test_can_create_mitra_assignment()
    {
        // Create required records
        $mitra = Mitra::factory()->create();
        $kegiatan = Kegiatan::factory()->create([
            'rate_pcl' => 100000,
            'rate_pml' => 150000,
            'rate_entri' => 50000
        ]);

        $assignmentData = [
            'kegiatan_id' => $kegiatan->id,
            'jabatan_tugas' => 'PCL',
            'mitra_id' => $mitra->id,
            'volume' => 10,
            'bln_bayar' => '2023-01-01'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/kantor/mitra/penugasan', $assignmentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'kegiatan_id',
                    'jabatan_tugas',
                    'mitra_id',
                    'volume',
                    'nilai',
                    'bln_bayar',
                    'created_by'
                ]
            ])
            ->assertJsonPath('data.nilai', 1000000) // 10 * 100000
            ->assertJsonPath('data.created_by', $this->user->id);

        $this->assertDatabaseHas('penugasan', [
            'kegiatan_id' => $kegiatan->id,
            'jabatan_tugas' => 'PCL',
            'mitra_id' => $mitra->id,
            'volume' => 10,
            'nilai' => 1000000
        ]);
    }

    public function test_can_list_mitra_with_penugasan_count()
    {
        // Create mitra records
        $mitra1 = Mitra::factory()->create(['nama_lengkap' => 'Budi Santoso']);
        $mitra2 = Mitra::factory()->create(['nama_lengkap' => 'Andi Saputra']);

        // Create penugasan records for mitra1
        Penugasan::factory()->count(3)->create(['mitra_id' => $mitra1->id]);
        // Create penugasan records for mitra2
        Penugasan::factory()->count(2)->create(['mitra_id' => $mitra2->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/mitra');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'nama_lengkap',
                        'penugasan_count'
                    ]
                ]
            ])
            ->assertJsonFragment([
                'id' => $mitra1->id,
                'nama_lengkap' => 'Budi Santoso',
                'penugasan_count' => 3
            ])
            ->assertJsonFragment([
                'id' => $mitra2->id,
                'nama_lengkap' => 'Andi Saputra',
                'penugasan_count' => 2
            ]);

        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_cannot_create_mitra_assignment_without_authentication()
    {
        $assignmentData = [
            'kegiatan_id' => 1,
            'jabatan_tugas' => 'PCL',
            'mitra_id' => 1,
            'volume' => 10,
            'bln_bayar' => '2023-01-01'
        ];

        $response = $this->postJson('/api/kantor/mitra/penugasan', $assignmentData);

        $response->assertStatus(401);
    }

    public function test_cannot_list_mitra_without_authentication()
    {
        $response = $this->getJson('/api/kantor/mitra');

        $response->assertStatus(401);
    }

    public function test_cannot_get_mitra_filters_without_authentication()
    {
        $response = $this->getJson('/api/kantor/mitra/filters');

        $response->assertStatus(401);
    }

    public function test_cannot_get_penugasan_options_without_authentication()
    {
        $response = $this->getJson('/api/kantor/mitra/penugasan-options');

        $response->assertStatus(401);
    }

    public function test_validation_error_when_creating_assignment_with_invalid_data()
    {
        // Create required records
        $mitra = Mitra::factory()->create();
        $kegiatan = Kegiatan::factory()->create();

        $invalidAssignmentData = [
            'kegiatan_id' => 999, // Non-existent kegiatan
            'jabatan_tugas' => 'INVALID', // Invalid position
            'mitra_id' => 999, // Non-existent mitra
            'volume' => -5, // Negative volume
            'bln_bayar' => 'invalid-date' // Invalid date
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/kantor/mitra/penugasan', $invalidAssignmentData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }
}
