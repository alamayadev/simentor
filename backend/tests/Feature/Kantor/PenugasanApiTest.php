<?php

namespace Tests\Feature\Kantor;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Penugasan;
use App\Models\Kegiatan;
use App\Models\Mitra;

class PenugasanApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    public function test_can_list_penugasan()
    {
        // Create some penugasan records
        // Create some penugasan records for current month to ensure they appear in default list
        Penugasan::factory()->count(3)->create([
            'bln_bayar' => now()->startOfMonth()->format('Y-m-d'),
            'kegiatan_id' => Kegiatan::factory()->create(['tahun' => date('Y')])
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta',
                'links'
            ]);
    }

    public function test_can_get_penugasan_filters()
    {
        $currentMonth = now()->startOfMonth()->format('Y-m-d');
        $nextMonth = now()->addMonth()->startOfMonth()->format('Y-m-d');

        // Create kegiatan records for current year
        $kegiatan1 = Kegiatan::factory()->create(['nama' => 'Sensus Penduduk 2020', 'tahun' => date('Y')]);
        $kegiatan2 = Kegiatan::factory()->create(['nama' => 'Survei Harga Konsumen', 'tahun' => date('Y')]);
        $kegiatan3 = Kegiatan::factory()->create(['nama' => 'Survei Pertanian', 'tahun' => date('Y')]);

        // Create penugasan records with different bulan_bayar and kegiatan
        Penugasan::factory()->create([
            'bln_bayar' => $currentMonth,
            'kegiatan_id' => $kegiatan1->id
        ]);

        Penugasan::factory()->create([
            'bln_bayar' => $currentMonth,
            'kegiatan_id' => $kegiatan2->id
        ]);

        Penugasan::factory()->create([
            'bln_bayar' => $nextMonth,
            'kegiatan_id' => $kegiatan1->id
        ]);

        // Create a kegiatan without any penugasan
        $kegiatanWithoutPenugasan = Kegiatan::factory()->create(['nama' => 'Kegiatan Tanpa Penugasan', 'tahun' => date('Y')]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan/filters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'blnBayarList',
                    'kegiatanList'
                ]
            ])
            ->assertJsonCount(2, 'data.blnBayarList')
            ->assertJsonCount(2, 'data.kegiatanList') // Only kegiatan with penugasan
            ->assertJsonFragment([
                'id' => $kegiatan1->id,
                'nama' => 'Sensus Penduduk 2020',
            ])
            ->assertJsonFragment([
                'id' => $kegiatan2->id,
                'nama' => 'Survei Harga Konsumen',
            ])
            ->assertJsonMissing([
                'id' => $kegiatanWithoutPenugasan->id,
                'nama' => 'Kegiatan Tanpa Penugasan'
            ]);

        // Verify that blnBayarList is ordered by bulan_bayar DESC
        $blnBayarList = $response->json('data.blnBayarList');
        $this->assertEquals($nextMonth, $blnBayarList[0]);
        $this->assertEquals($currentMonth, $blnBayarList[1]);

        // Verify that the dates are in the correct format (YYYY-MM-DD) without timezone info
        foreach ($blnBayarList as $date) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
        }
    }

    public function test_can_filter_penugasan_by_kegiatan()
    {
        // Create kegiatan records for current year
        $kegiatan1 = Kegiatan::factory()->create(['tahun' => date('Y')]);
        $kegiatan2 = Kegiatan::factory()->create(['tahun' => date('Y')]);

        // Create penugasan records
        Penugasan::factory()->create(['kegiatan_id' => $kegiatan1->id]);
        Penugasan::factory()->create(['kegiatan_id' => $kegiatan1->id]);
        Penugasan::factory()->create(['kegiatan_id' => $kegiatan2->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan?filter[kegiatan_id]=' . $kegiatan1->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_penugasan_by_bln_bayar()
    {
        $date1 = now()->startOfMonth()->format('Y-m-d');
        $date2 = now()->addMonth()->startOfMonth()->format('Y-m-d');

        // Create kegiatan for current year
        $kegiatan = Kegiatan::factory()->create(['tahun' => date('Y')]);

        // Create penugasan records with different bulan_bayar
        Penugasan::factory()->create(['bln_bayar' => $date1, 'kegiatan_id' => $kegiatan->id]);
        Penugasan::factory()->create(['bln_bayar' => $date1, 'kegiatan_id' => $kegiatan->id]);
        Penugasan::factory()->create(['bln_bayar' => $date2, 'kegiatan_id' => $kegiatan->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan?filter[bln_bayar]=' . $date1);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_penugasan_by_jabatan_tugas()
    {
        // Create kegiatan for current year
        $kegiatan = Kegiatan::factory()->create(['tahun' => date('Y')]);

        // Create penugasan records
        Penugasan::factory()->create(['jabatan_tugas' => 'PCL', 'kegiatan_id' => $kegiatan->id]);
        Penugasan::factory()->create(['jabatan_tugas' => 'PCL', 'kegiatan_id' => $kegiatan->id]);
        Penugasan::factory()->create(['jabatan_tugas' => 'PML', 'kegiatan_id' => $kegiatan->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan?filter[jabatan_tugas]=PCL');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_penugasan_by_mitra()
    {
        // Create kegiatan for current year
        $kegiatan = Kegiatan::factory()->create(['tahun' => date('Y')]);

        // Create mitra records
        $mitra1 = Mitra::factory()->create();
        $mitra2 = Mitra::factory()->create();

        // Create penugasan records
        Penugasan::factory()->create(['mitra_id' => $mitra1->id, 'kegiatan_id' => $kegiatan->id]);
        Penugasan::factory()->create(['mitra_id' => $mitra1->id, 'kegiatan_id' => $kegiatan->id]);
        Penugasan::factory()->create(['mitra_id' => $mitra2->id, 'kegiatan_id' => $kegiatan->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan?filter[mitra_id]=' . $mitra1->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_show_penugasan()
    {
        // Create a penugasan record
        $penugasan = Penugasan::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan/' . $penugasan->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $penugasan->id);
    }

    public function test_can_create_penugasan()
    {
        // Create required records
        $kegiatan = Kegiatan::factory()->create([
            'rate_pcl' => 50000  // Set specific rate to ensure nilai = 100 * 50000 = 5000000
        ]);
        $mitra = Mitra::factory()->create();

        $penugasanData = [
            'kegiatan_id' => $kegiatan->id,
            'jabatan_tugas' => 'PCL',
            'mitra_id' => $mitra->id,
            'volume' => 100,
            'nilai' => 5000000,
            'bln_bayar' => '2024-01-01'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/kantor/penugasan', $penugasanData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
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
            ->assertJsonPath('data.created_by', $this->user->id)
            ->assertJsonPath('data.nilai', 5000000);

        $this->assertDatabaseHas('penugasan', [
            'kegiatan_id' => $kegiatan->id,
            'jabatan_tugas' => 'PCL',
            'mitra_id' => $mitra->id,
            'volume' => 100,
            'nilai' => 5000000,
            'bln_bayar' => '2024-01-01',
            'created_by' => $this->user->id
        ]);
    }

    public function test_can_update_penugasan()
    {
        // Create required records with specific rates
        $kegiatan = Kegiatan::factory()->create([
            'rate_pcl' => 50000  // Set specific rate to ensure nilai = 200 * 50000 = 10000000
        ]);
        $mitra = Mitra::factory()->create();
        
        // Create a penugasan record with the specific kegiatan
        $penugasan = Penugasan::factory()->create([
            'kegiatan_id' => $kegiatan->id,
            'jabatan_tugas' => 'PCL',
            'created_by' => $this->user->id
        ]);

        $updatedData = [
            'volume' => 200,
            // nilai will be recalculated as 200 * 50000 = 10000000
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/kantor/penugasan/' . $penugasan->id, $updatedData);

        $response->assertStatus(200);
        
        $response->assertStatus(200)
            ->assertJsonPath('data.volume', 200)
            ->assertJsonPath('data.nilai', 10000000); // 200 * 50000 = 10000000

        $this->assertDatabaseHas('penugasan', [
            'id' => $penugasan->id,
            'volume' => 200,
            'nilai' => 10000000  // Calculated as 200 * 50000
        ]);
    }

    public function test_cannot_update_penugasan_created_by_other_user()
    {
        // Create another user
        $otherUser = User::factory()->create();

        // Create a penugasan record created by another user
        $penugasan = Penugasan::factory()->create([
            'created_by' => $otherUser->id
        ]);

        $updatedData = [
            'volume' => 200,
            'nilai' => 6000000
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/kantor/penugasan/' . $penugasan->id, $updatedData);

        $response->assertStatus(403);
    }

    public function test_can_delete_penugasan()
    {
        // Create a penugasan record
        $penugasan = Penugasan::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/kantor/penugasan/' . $penugasan->id);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Penugasan berhasil dihapus');

        $this->assertDatabaseMissing('penugasan', [
            'id' => $penugasan->id
        ]);
    }

    public function test_cannot_list_penugasan_without_authentication()
    {
        $response = $this->getJson('/api/kantor/penugasan');

        $response->assertStatus(401);
    }

    public function test_cannot_get_penugasan_filters_without_authentication()
    {
        $response = $this->getJson('/api/kantor/penugasan/filters');

        $response->assertStatus(401);
    }

    public function test_cannot_show_penugasan_without_authentication()
    {
        // Create a penugasan record
        $penugasan = Penugasan::factory()->create();

        $response = $this->getJson('/api/kantor/penugasan/' . $penugasan->id);

        $response->assertStatus(401);
    }

    public function test_cannot_create_penugasan_without_authentication()
    {
        $penugasanData = [
            'kegiatan_id' => 1,
            'jabatan_tugas' => 'PCL',
            'mitra_id' => 1,
            'volume' => 100,
            'nilai' => 5000000
        ];

        $response = $this->postJson('/api/kantor/penugasan', $penugasanData);

        $response->assertStatus(401);
    }

    public function test_cannot_update_penugasan_without_authentication()
    {
        // Create a penugasan record
        $penugasan = Penugasan::factory()->create();

        $updatedData = [
            'volume' => 200,
            'nilai' => 6000000
        ];

        $response = $this->putJson('/api/kantor/penugasan/' . $penugasan->id, $updatedData);

        $response->assertStatus(401);
    }

    public function test_cannot_delete_penugasan_without_authentication()
    {
        // Create a penugasan record
        $penugasan = Penugasan::factory()->create();

        $response = $this->deleteJson('/api/kantor/penugasan/' . $penugasan->id);

        $response->assertStatus(401);
    }

    public function test_can_get_mitra_options()
    {
        // Create mitra records
        $mitra1 = Mitra::factory()->create(['nama_lengkap' => 'Budi Santoso']);
        $mitra2 = Mitra::factory()->create(['nama_lengkap' => 'Andi Saputra']);
        $mitra3 = Mitra::factory()->create(['nama_lengkap' => 'Siti Rahayu']);

        // Create penugasan records for only some mitra
        Penugasan::factory()->create(['mitra_id' => $mitra1->id]);
        Penugasan::factory()->create(['mitra_id' => $mitra2->id]);
        // Note: $mitra3 does not have any penugasan

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan/mitra-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'mitraOptions'
                ]
            ])
            ->assertJsonCount(2, 'data.mitraOptions');

        // Check that each mitra has the expected fields
        $responseData = $response->json();
        $mitraOptions = $responseData['data']['mitraOptions'];

        $this->assertEquals($mitra1->id, $mitraOptions[0]['id']);
        $this->assertEquals('Budi Santoso', $mitraOptions[0]['nama_lengkap']);

        $this->assertEquals($mitra2->id, $mitraOptions[1]['id']);
        $this->assertEquals('Andi Saputra', $mitraOptions[1]['nama_lengkap']);

        // Verify that mitra3 (which has no penugasan) is not included
        foreach ($mitraOptions as $mitra) {
            $this->assertNotEquals($mitra3->id, $mitra['id']);
        }
    }

    public function test_cannot_get_mitra_options_without_authentication()
    {
        $response = $this->getJson('/api/kantor/penugasan/mitra-options');

        $response->assertStatus(401);
    }

    public function test_validation_error_when_creating_penugasan_with_invalid_data()
    {
        $invalidPenugasanData = [
            'kegiatan_id' => 999, // Non-existent kegiatan
            'jabatan_tugas' => '', // Required field
            'mitra_id' => 999, // Non-existent mitra
            'volume' => 'invalid', // Invalid type
            'nilai' => 'invalid' // Invalid type
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/kantor/penugasan', $invalidPenugasanData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    public function test_can_insert_penugasan()
    {
        // Create base penugasan
        $basePenugasan = Penugasan::factory()->create([
            'created_by' => $this->user->id
        ]);

        $insertData = [
            'mitra_id' => Mitra::factory()->create()->id,
            'volume' => 150
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/kantor/penugasan/' . $basePenugasan->id . '/insert', $insertData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('penugasan', [
            'mitra_id' => $insertData['mitra_id'],
            'volume' => $insertData['volume']
        ]);
    }

    public function test_cannot_insert_penugasan_without_authentication()
    {
        $basePenugasan = Penugasan::factory()->create();

        $insertData = [
            'mitra_id' => Mitra::factory()->create()->id,
            'volume' => 150
        ];

        $response = $this->postJson('/api/kantor/penugasan/' . $basePenugasan->id . '/insert', $insertData);

        $response->assertStatus(401);
    }

    public function test_can_get_mitra_dropdown()
    {
        // Create mitra records
        Mitra::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan/mitra-dropdown');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'sobat_id',
                        'nama_lengkap',
                        'nik',
                        'keca'
                    ]
                ]
            ]);
    }

    public function test_cannot_get_mitra_dropdown_without_authentication()
    {
        $response = $this->getJson('/api/kantor/penugasan/mitra-dropdown');

        $response->assertStatus(401);
    }

    public function test_can_get_form_options()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan/form-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'jabatanTugasOptions',
                    'fungsiOptions'
                ]
            ])
            ->assertJsonCount(count(\App\Enums\JabatanTugasType::cases()), 'data.jabatanTugasOptions');
    }

    public function test_cannot_get_form_options_without_authentication()
    {
        $response = $this->getJson('/api/kantor/penugasan/form-options');

        $response->assertStatus(401);
    }

    public function test_can_get_kegiatan_options()
    {
        // Create kegiatan with specific fungsi, current year, and future end date
        Kegiatan::factory()->create([
            'fungsi' => 'Umum',
            'tahun' => date('Y'),
            'tgl_selesai' => now()->addMonths(2)->format('Y-m-d')
        ]);
        Kegiatan::factory()->create([
            'fungsi' => 'Umum',
            'tahun' => date('Y'),
            'tgl_selesai' => now()->addMonths(2)->format('Y-m-d')
        ]);
        Kegiatan::factory()->create([
            'fungsi' => 'Distribusi',
            'tahun' => date('Y'),
            'tgl_selesai' => now()->addMonths(2)->format('Y-m-d')
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan/kegiatan-options?fungsi=Umum');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'kegiatanOptions' => [
                        '*' => [
                            'id',
                            'nama'
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.kegiatanOptions'); // Only 2 records with 'Umum'
    }

    public function test_cannot_get_kegiatan_options_without_fungsi_parameter()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/penugasan/kegiatan-options');

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Fungsi parameter is required.');
    }

    public function test_cannot_get_kegiatan_options_without_authentication()
    {
        $response = $this->getJson('/api/kantor/penugasan/kegiatan-options?fungsi=Umum');

        $response->assertStatus(401);
    }
}
