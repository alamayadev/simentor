<?php

namespace Tests\Feature\Kantor;

use App\Enums\FungsiType;
use App\Enums\JenisKegiatanType;
use App\Enums\SatuanType;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Penugasan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function actingAsUser()
    {
        return $this->actingAs($this->user, 'sanctum');
    }

    private function createKegiatan(array $overrides = []): Kegiatan
    {
        return Kegiatan::factory()->create($overrides);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'tahun' => date('Y'),
            'fungsi' => FungsiType::PRODUKSI->value,
            'kode_kegiatan' => '01.02.03.04',
            'nama' => 'Kegiatan Statistik ' . date('Y'),
            'tgl_mulai' => date('Y') . '-01-01',
            'tgl_selesai' => date('Y') . '-12-31',
            'jenis_kegiatan' => JenisKegiatanType::PENGUMPULAN_DATA->value,
            'jml_ptgs' => 10,
            'volume' => 100,
            'satuan' => SatuanType::Resp->value,
            'rate_pcl' => 100,
            'rate_pml' => 0,
            'rate_entri' => 0,
        ], $overrides);
    }

    private function assertKegiatanDataStructure($response): void
    {
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'tahun',
                'fungsi',
                'kode_kegiatan',
                'nama',
                'tgl_mulai',
                'tgl_selesai',
                'jenis_kegiatan',
                'jml_ptgs',
                'volume',
                'satuan',
                'rate_pcl',
                'rate_pml',
                'rate_entri',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    // ==================== GET /api/kantor/kegiatan/filter-list ====================

    public function test_unauthenticated_user_cannot_get_filter_list()
    {
        $response = $this->getJson('/api/kantor/kegiatan/filter-list');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_authenticated_user_can_get_filter_list()
    {
        $this->createKegiatan([
            'fungsi' => FungsiType::UMUM->value,
            'tahun' => '2023',
            'jenis_kegiatan' => JenisKegiatanType::PERSIAPAN->value,
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/filter-list');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'fungsiList',
                    'tahunList',
                    'jenisList',
                ],
            ]);
    }

    public function test_filter_list_includes_created_values()
    {
        $this->createKegiatan([
            'fungsi' => FungsiType::DISTRIBUSI->value,
            'tahun' => '2022',
            'jenis_kegiatan' => JenisKegiatanType::DISEMINASI->value,
        ]);

        $this->createKegiatan([
            'fungsi' => FungsiType::PRODUKSI->value,
            'tahun' => date('Y'),
            'jenis_kegiatan' => JenisKegiatanType::PENGOLAHAN->value,
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/filter-list');

        $response->assertOk()
            ->assertJsonFragment(['fungsiList' => [FungsiType::DISTRIBUSI->value, FungsiType::PRODUKSI->value]])
            ->assertJsonFragment(['tahunList' => ['2022', date('Y')]])
            ->assertJsonFragment(['jenisList' => [JenisKegiatanType::DISEMINASI->value, JenisKegiatanType::PENGOLAHAN->value]]);
    }

    // ==================== GET /api/kantor/kegiatan/calendar ====================

    public function test_unauthenticated_user_cannot_get_calendar()
    {
        $response = $this->getJson('/api/kantor/kegiatan/calendar');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_can_get_kegiatan_calendar()
    {
        $kegiatanBerjalan = $this->createKegiatan([
            'nama' => 'Sensus Penduduk ' . date('Y'),
            'tahun' => date('Y'),
            'tgl_mulai' => now()->subDays(10)->format('Y-m-d'),
            'tgl_selesai' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $kegiatanAkanDatang = $this->createKegiatan([
            'nama' => 'Sensus Pertanian ' . date('Y'),
            'tahun' => date('Y'),
            'tgl_mulai' => now()->addDays(30)->format('Y-m-d'),
            'tgl_selesai' => now()->addDays(60)->format('Y-m-d'),
        ]);

        $kegiatanSudahSelesai = $this->createKegiatan([
            'nama' => 'Survei Harga ' . date('Y'),
            'tahun' => date('Y'),
            'tgl_mulai' => now()->subDays(60)->format('Y-m-d'),
            'tgl_selesai' => now()->subDays(30)->format('Y-m-d'),
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/calendar');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'kegiatan_berjalan' => [
                        '*' => ['id', 'fungsi', 'nama', 'tgl_mulai', 'tgl_selesai', 'volume', 'satuan', 'penugasan_sum_volume'],
                    ],
                    'kegiatan_akan_datang' => [
                        '*' => ['id', 'fungsi', 'nama', 'tgl_mulai', 'tgl_selesai', 'volume', 'satuan', 'penugasan_sum_volume'],
                    ],
                    'kegiatan_sudah_selesai' => [
                        '*' => ['id', 'fungsi', 'nama', 'tgl_mulai', 'tgl_selesai', 'volume', 'satuan', 'penugasan_sum_volume'],
                    ],
                ],
            ])
            ->assertJsonFragment(['id' => $kegiatanBerjalan->id, 'nama' => 'Sensus Penduduk ' . date('Y')])
            ->assertJsonFragment(['id' => $kegiatanAkanDatang->id, 'nama' => 'Sensus Pertanian ' . date('Y')])
            ->assertJsonFragment(['id' => $kegiatanSudahSelesai->id, 'nama' => 'Survei Harga ' . date('Y')]);
    }

    // ==================== GET /api/kantor/kegiatan/form-options ====================

    public function test_unauthenticated_user_cannot_get_form_options()
    {
        $response = $this->getJson('/api/kantor/kegiatan/form-options');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_form_options_includes_enums()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/form-options');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['satuan', 'fungsi', 'jenis_kegiatan'],
            ]);

        $this->assertEquals(
            array_column(SatuanType::cases(), 'value'),
            $response->json('data.satuan')
        );
        $this->assertEquals(
            array_column(FungsiType::cases(), 'value'),
            $response->json('data.fungsi')
        );
        $this->assertEquals(
            array_column(JenisKegiatanType::cases(), 'value'),
            $response->json('data.jenis_kegiatan')
        );
    }

    // ==================== GET /api/kantor/kegiatan/by-year ====================

    public function test_unauthenticated_user_cannot_get_kegiatan_by_year()
    {
        $response = $this->getJson('/api/kantor/kegiatan/by-year?tahun=2024');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_authenticated_user_can_get_kegiatan_by_year()
    {
        $year = date('Y');
        $this->createKegiatan(['tahun' => $year]);
        $this->createKegiatan(['tahun' => (string)($year - 1)]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/by-year?tahun=' . $year);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tahun', $year);
    }

    public function test_kegiatan_by_year_defaults_to_current_year()
    {
        $currentYear = date('Y');
        $this->createKegiatan(['tahun' => $currentYear]);
        $this->createKegiatan(['tahun' => '2022']);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/by-year');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tahun', $currentYear);
    }

    // ==================== GET /api/kantor/kegiatan/statistics ====================

    public function test_unauthenticated_user_cannot_get_statistics()
    {
        $response = $this->getJson('/api/kantor/kegiatan/statistics');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_statistics_returns_expected_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'kegiatan_by_fungsi',
                    'nilai_penugasan_by_month',
                    'top_mitra_honor',
                    'kegiatan_penyerapan_100',
                ],
            ]);
    }

    public function test_statistics_calculations_are_correct()
    {
        $kegiatan = $this->createKegiatan([
            'tahun' => date('Y'),
            'fungsi' => FungsiType::PRODUKSI->value,
            'volume' => 10,
            'rate_pcl' => 1000,
            'rate_pml' => 0,
            'rate_entri' => 0,
        ]);

        $mitra = Mitra::factory()->create(['nama_lengkap' => 'Mitra Satu', 'keca' => 'KLARI']);

        Penugasan::factory()->create([
            'kegiatan_id' => $kegiatan->id,
            'mitra_id' => $mitra->id,
            'nilai' => 5000,
            'bln_bayar' => date('Y') . '-01-01',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/statistics');

        $response->assertOk();

        $kegiatanByFungsi = collect($response->json('data.kegiatan_by_fungsi'))
            ->firstWhere('fungsi', FungsiType::PRODUKSI->value);

        $this->assertNotNull($kegiatanByFungsi);
        $this->assertEquals(10000, $kegiatanByFungsi['total_anggaran']);
        $this->assertEquals(5000, $kegiatanByFungsi['total_penyerapan']);
        $this->assertEquals(50.0, $kegiatanByFungsi['persen']);

        $monthly = $response->json('data.nilai_penugasan_by_month');
        $this->assertNotEmpty($monthly);
    }

    // ==================== GET /api/kantor/kegiatan (index) ====================

    public function test_unauthenticated_user_cannot_get_kegiatan_index()
    {
        $response = $this->getJson('/api/kantor/kegiatan');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_authenticated_user_can_get_kegiatan_index()
    {
        // Ensure created activities are for current year to be listed by default
        $this->createKegiatan(['tahun' => date('Y')]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan?per_page=5');

        $response->assertOk();
        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_kegiatan_index_supports_search_and_filters()
    {
        $this->createKegiatan([
            'nama' => 'Sensus Pertanian',
            'fungsi' => FungsiType::PRODUKSI->value,
            'tahun' => date('Y'),
        ]);

        $this->createKegiatan([
            'nama' => 'Survei Sosial',
            'fungsi' => FungsiType::SOSIAL->value,
            'tahun' => (string)(date('Y') - 1),
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan?search=Sensus&fungsi=' . FungsiType::PRODUKSI->value . '&filter[tahun]=' . date('Y'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Sensus Pertanian');
    }

    public function test_kegiatan_index_sorts_by_nama_asc()
    {
        $first = $this->createKegiatan(['nama' => 'AAA Kegiatan', 'tahun' => date('Y')]);
        $second = $this->createKegiatan(['nama' => 'ZZZ Kegiatan', 'tahun' => date('Y')]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan?sort=nama');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $first->id)
            ->assertJsonPath('data.1.id', $second->id);
    }

    public function test_kegiatan_index_includes_penugasan_counts()
    {
        $kegiatan = $this->createKegiatan(['volume' => 10, 'tahun' => date('Y')]);

        Penugasan::factory()->count(2)->create([
            'kegiatan_id' => $kegiatan->id,
            'volume' => 5,
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan?per_page=5');

        $response->assertOk()
            ->assertJsonPath('data.0.jml_penugasan', 2)
            ->assertJsonPath('data.0.penugasan_sum_volume', 10);
    }

    // ==================== GET /api/kantor/kegiatan/{id} ====================

    public function test_unauthenticated_user_cannot_show_kegiatan()
    {
        $kegiatan = $this->createKegiatan();

        $response = $this->getJson('/api/kantor/kegiatan/' . $kegiatan->id);

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_authenticated_user_can_show_kegiatan()
    {
        $kegiatan = $this->createKegiatan(['tahun' => date('Y')]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/' . $kegiatan->id);

        $response->assertOk();
        $this->assertKegiatanDataStructure($response);
        $response->assertJsonPath('data.id', $kegiatan->id);
    }

    public function test_show_returns_404_when_kegiatan_missing()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/99999');

        $response->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Kegiatan not found']);
    }

    public function test_show_includes_penugasan_aggregates()
    {
        $kegiatan = $this->createKegiatan(['tahun' => date('Y')]);
        Penugasan::factory()->count(3)->create([
            'kegiatan_id' => $kegiatan->id,
            'volume' => 2,
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/' . $kegiatan->id);

        $response->assertOk()
            ->assertJsonPath('data.jml_penugasan', 3)
            ->assertJsonPath('data.penugasan_sum_volume', 6);
    }

    // ==================== POST /api/kantor/kegiatan ====================

    public function test_unauthenticated_user_cannot_store_kegiatan()
    {
        $response = $this->postJson('/api/kantor/kegiatan', $this->validPayload());

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_store_validation_missing_required_fields()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'tahun',
                'fungsi',
                'kode_kegiatan',
                'nama',
                'tgl_mulai',
                'tgl_selesai',
                'jenis_kegiatan',
                'jml_ptgs',
                'volume',
                'satuan',
            ]);
    }

    public function test_store_validation_invalid_values()
    {
        $payload = $this->validPayload([
            'tahun' => '24',
            'fungsi' => 'INVALID',
            'kode_kegiatan' => '01.02',
            'nama' => 'Short',
            'tgl_mulai' => '01-01-2024',
            'tgl_selesai' => '31-12-2024',
            'jenis_kegiatan' => 'INVALID',
            'jml_ptgs' => 0,
            'volume' => 0,
        ]);

        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'tahun',
                'fungsi',
                'kode_kegiatan',
                'nama',
                'tgl_mulai',
                'tgl_selesai',
                'jenis_kegiatan',
                'jml_ptgs',
                'volume',
            ]);
    }

    public function test_store_requires_at_least_one_rate()
    {
        $payload = $this->validPayload([
            'rate_pcl' => 0,
            'rate_pml' => 0,
            'rate_entri' => 0,
        ]);

        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rate_pcl', 'rate_pml', 'rate_entri']);
    }

    public function test_authenticated_user_can_store_kegiatan()
    {
        $payload = $this->validPayload(['nama' => 'Sensus Penduduk ' . date('Y')]);

        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan', $payload);

        $response->assertStatus(201);
        $this->assertKegiatanDataStructure($response);
        $response->assertJsonPath('data.nama', 'Sensus Penduduk ' . date('Y'));

        $this->assertDatabaseHas('kegiatan', [
            'nama' => 'Sensus Penduduk ' . date('Y'),
            'tahun' => date('Y'),
        ]);
    }

    // ==================== PUT /api/kantor/kegiatan/{id} ====================

    public function test_unauthenticated_user_cannot_update_kegiatan()
    {
        $kegiatan = $this->createKegiatan();

        $response = $this->putJson('/api/kantor/kegiatan/' . $kegiatan->id, [
            'nama' => 'Updated Kegiatan',
        ]);

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_update_returns_404_when_kegiatan_missing()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/kantor/kegiatan/99999', $this->validPayload());

        $response->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Kegiatan not found']);
    }

    public function test_update_validation_invalid_values()
    {
        $kegiatan = $this->createKegiatan();

        $payload = [
            'tahun' => '24',
            'fungsi' => 'INVALID',
            'kode_kegiatan' => '01.02',
            'nama' => 'Short',
            'tgl_mulai' => '01-01-2024',
            'tgl_selesai' => '31-12-2024',
            'jenis_kegiatan' => 'INVALID',
            'jml_ptgs' => 0,
            'volume' => 0,
        ];

        $response = $this->actingAsUser()
            ->putJson('/api/kantor/kegiatan/' . $kegiatan->id, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'tahun',
                'fungsi',
                'kode_kegiatan',
                'nama',
                'tgl_mulai',
                'tgl_selesai',
                'jenis_kegiatan',
                'jml_ptgs',
                'volume',
            ]);
    }

    public function test_update_requires_at_least_one_rate()
    {
        $kegiatan = $this->createKegiatan([
            'rate_pcl' => 0,
            'rate_pml' => 0,
            'rate_entri' => 0,
        ]);

        $response = $this->actingAsUser()
            ->putJson('/api/kantor/kegiatan/' . $kegiatan->id, ['nama' => 'Updated Kegiatan']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rate_pcl', 'rate_pml', 'rate_entri']);
    }

    public function test_authenticated_user_can_update_kegiatan()
    {
        $kegiatan = $this->createKegiatan(['nama' => 'Old Kegiatan']);

        $response = $this->actingAsUser()
            ->putJson('/api/kantor/kegiatan/' . $kegiatan->id, [
                'nama' => 'Updated Kegiatan',
                'jenis_kegiatan' => JenisKegiatanType::DISEMINASI->value,
            ]);

        $response->assertOk();
        $this->assertKegiatanDataStructure($response);
        $response->assertJsonPath('data.nama', 'Updated Kegiatan');

        $this->assertDatabaseHas('kegiatan', [
            'id' => $kegiatan->id,
            'nama' => 'Updated Kegiatan',
        ]);
    }

    // ==================== DELETE /api/kantor/kegiatan/{id} ====================

    public function test_unauthenticated_user_cannot_delete_kegiatan()
    {
        $kegiatan = $this->createKegiatan();

        $response = $this->deleteJson('/api/kantor/kegiatan/' . $kegiatan->id);

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_delete_returns_404_when_kegiatan_missing()
    {
        $response = $this->actingAsUser()
            ->deleteJson('/api/kantor/kegiatan/99999');

        $response->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Kegiatan not found']);
    }

    public function test_authenticated_user_can_delete_kegiatan()
    {
        $kegiatan = $this->createKegiatan();

        $response = $this->actingAsUser()
            ->deleteJson('/api/kantor/kegiatan/' . $kegiatan->id);

        $response->assertOk()
            ->assertJson(['success' => true, 'message' => 'Kegiatan berhasil dihapus']);

        $this->assertDatabaseMissing('kegiatan', [
            'id' => $kegiatan->id,
        ]);
    }
}
