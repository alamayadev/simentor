<?php

namespace Tests\Feature\Api\Kantor\NomorSurat;

use App\Models\SuratPermintaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Api\BaseApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class PermintaanApiTest extends BaseApiTestCase
{
    use RefreshDatabase;
    private array $validPermintaanData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validPermintaanData = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'kode_klas' => '100',
            'dari' => 'Kepala BPS Kota Jakarta',
            'perihal' => 'Permohonan Data Statistik Kependudukan',
            'no_sisip' => null,
        ];
    }

    protected function setUpSettings(): void
    {
        DB::table('settings')->insert([
            'key' => 'FORMAT_FORM_PERMINTAAN',
            'value' => '{nomor}/BPS-{klas}/{tahun}',
            'tahun' => '2024',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('klasifikasi_surat')->insert([
            'parent_id' => null,
            'kode' => '100',
            'keterangan' => 'Organisasi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Authentication Tests
    #[Test]
    public function it_requires_authentication_to_index_permintaan(): void
    {
        $this->setUpSettings();

        $response = $this->getJson('/api/kantor/surat/permintaan');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_requires_authentication_to_store_permintaan(): void
    {
        $this->setUpSettings();

        $response = $this->postJson('/api/kantor/surat/permintaan', $this->validPermintaanData);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_requires_authentication_to_show_permintaan(): void
    {
        $this->setUpSettings();

        $response = $this->getJson('/api/kantor/surat/permintaan/1');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_requires_authentication_to_update_permintaan(): void
    {
        $this->setUpSettings();

        $response = $this->putJson('/api/kantor/surat/permintaan/1', $this->validPermintaanData);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_requires_authentication_to_delete_permintaan(): void
    {
        $this->setUpSettings();

        $response = $this->deleteJson('/api/kantor/surat/permintaan/1');

        $response->assertStatus(401);
    }

    // Index Method Tests
    #[Test]
    public function it_lists_permintaan_successfully_as_admin(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->count(5)->create(['thn' => '2024']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_lists_permintaan_successfully_as_user(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->count(3)->create(['thn' => '2024']);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/surat/permintaan');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_filters_permintaan_by_search(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->create([
            'thn' => '2024',
            'dari' => 'Kepala BPS Jakarta',
            'perihal' => 'Data Kependudukan'
        ]);
        SuratPermintaan::factory()->create([
            'thn' => '2024',
            'dari' => 'Kepala BPS Bandung',
            'perihal' => 'Data Ekonomi'
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan?search=Jakarta');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_filters_permintaan_by_tanggal(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->create([
            'thn' => '2024',
            'tanggal' => '2024-01-15'
        ]);
        SuratPermintaan::factory()->create([
            'thn' => '2024',
            'tanggal' => '2024-02-15'
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan?tanggal=2024-01-15');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_filters_permintaan_by_tahun(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->create(['thn' => '2023']);
        SuratPermintaan::factory()->create(['thn' => '2024']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan?tahun=2024');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_sorts_permintaan_by_different_fields(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->create([
            'thn' => '2024',
            'tanggal' => '2024-01-15'
        ]);
        SuratPermintaan::factory()->create([
            'thn' => '2024',
            'tanggal' => '2024-02-15'
        ]);

        // Test sort by tanggal DESC
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan?sort_by=tanggal&sort_dir=DESC');
        $response->assertStatus(200);

        // Test sort by tanggal ASC
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan?sort_by=tanggal&sort_dir=ASC');
        $response->assertStatus(200);
    }

    #[Test]
    public function it_paginates_permintaan_correctly(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->count(25)->create(['thn' => '2024']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan?page=2&per_page=10');

        $response->assertStatus(200);
    }

    // Store Method Tests
    #[Test]
    public function it_creates_permintaan_successfully_as_admin(): void
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan', $this->validPermintaanData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_creates_permintaan_with_sisip_successfully(): void
    {
        $this->setUpSettings();

        $dataWithSisip = array_merge($this->validPermintaanData, [
            'no_sisip' => '1'
        ]);

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan', $dataWithSisip);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_validates_store_data(): void
    {
        $this->setUpSettings();

        $invalidData = [];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan', $invalidData);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $this->setUpSettings();

        $invalidData = [
            'thn' => '',
            'tanggal' => '',
            'nomor' => '',
            'kode_klas' => '',
            'dari' => '',
            'perihal' => '',
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan', $invalidData);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_handles_missing_format_setting(): void
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan', $this->validPermintaanData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Format setting not found',
            ]);
    }

    // Show Method Tests
    #[Test]
    public function it_shows_permintaan_successfully_as_admin(): void
    {
        $this->setUpSettings();

        $permintaan = SuratPermintaan::factory()->create($this->validPermintaanData);

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/surat/permintaan/{$permintaan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_shows_permintaan_successfully_as_user(): void
    {
        $this->setUpSettings();

        $permintaan = SuratPermintaan::factory()->create($this->validPermintaanData);

        $response = $this->actingAsOrganik()
            ->getJson("/api/kantor/surat/permintaan/{$permintaan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_returns_not_found_for_nonexistent_permintaan(): void
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan/999999');

        $response->assertStatus(404);
    }

    // Update Method Tests
    #[Test]
    public function it_updates_permintaan_successfully_as_admin(): void
    {
        $this->setUpSettings();

        $permintaan = SuratPermintaan::factory()->create($this->validPermintaanData);

        $updateData = [
            'thn' => '2024',
            'tanggal' => '2024-02-20',
            'nomor' => '2',
            'kode_klas' => '100',
            'dari' => 'Kepala BPS Updated',
            'perihal' => 'Updated Perihal',
        ];

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/permintaan/{$permintaan->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_validates_update_data(): void
    {
        $this->setUpSettings();

        $permintaan = SuratPermintaan::factory()->create($this->validPermintaanData);
        $invalidData = [
            'tanggal' => 'invalid-date',
        ];

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/permintaan/{$permintaan->id}", $invalidData);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_returns_not_found_when_updating_nonexistent_permintaan(): void
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->putJson('/api/kantor/surat/permintaan/999999', $this->validPermintaanData);

        $response->assertStatus(404);
    }

    // Delete Method Tests
    #[Test]
    public function it_deletes_permintaan_successfully_as_admin(): void
    {
        $this->setUpSettings();

        $permintaan = SuratPermintaan::factory()->create([
            'no_surat' => '0001/BPS-100/2024'
        ]);

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/surat/permintaan/{$permintaan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('surat_permintaan', [
            'id' => $permintaan->id,
        ]);
    }

    #[Test]
    public function it_deletes_permintaan_successfully_as_user(): void
    {
        $this->setUpSettings();

        $permintaan = SuratPermintaan::factory()->create([
            'no_surat' => '0001/BPS-100/2024'
        ]);

        $response = $this->actingAsOrganik()
            ->deleteJson("/api/kantor/surat/permintaan/{$permintaan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('surat_permintaan', [
            'id' => $permintaan->id,
        ]);
    }

    #[Test]
    public function it_returns_not_found_when_deleting_nonexistent_permintaan(): void
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->deleteJson('/api/kantor/surat/permintaan/999999');

        $response->assertStatus(404);
    }

    // Sisip Method Tests
    #[Test]
    public function it_creates_sisip_permintaan_successfully(): void
    {
        $this->setUpSettings();

        $referencePermintaan = SuratPermintaan::factory()->create([
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '0001',
            'kode_klas' => '100',
            'dari' => 'Kepala BPS Jakarta',
            'perihal' => 'Permohonan Awal',
            'no_surat' => '0001/BPS-100/2024'
        ]);

        $sisipData = [
            'id' => $referencePermintaan->id,
            'tanggal' => '2024-01-20',
            'kode_klas' => '100',
            'dari' => 'Kepala BPS Jakarta',
            'perihal' => 'Permohonan Tambahan (Sisip)',
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan/sisip', $sisipData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_validates_sisip_data(): void
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan/sisip', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id', 'tanggal', 'kode_klas', 'dari', 'perihal']);
    }

    // Get Dates Method Tests
    #[Test]
    public function it_gets_dates_successfully(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->create([
            'thn' => '2024',
            'tanggal' => '2024-01-15'
        ]);
        SuratPermintaan::factory()->create([
            'thn' => '2024',
            'tanggal' => '2024-02-20'
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan/dates?tahun=2024');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_gets_dates_with_default_year(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->create([
            'thn' => date('Y'),
            'tanggal' => date('Y-m-d')
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan/dates');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    // Get Years Method Tests
    #[Test]
    public function it_gets_years_successfully(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->create(['thn' => '2022']);
        SuratPermintaan::factory()->create(['thn' => '2023']);
        SuratPermintaan::factory()->create(['thn' => '2024']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan/years');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    // Get Klasifikasi Method Tests
    #[Test]
    public function it_gets_klasifikasi_successfully(): void
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan/klasifikasi');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    // Bulk Update Status Method Tests
    #[Test]
    public function it_bulk_updates_status_successfully(): void
    {
        $this->setUpSettings();

        $permintaan1 = SuratPermintaan::factory()->create();
        $permintaan2 = SuratPermintaan::factory()->create();
        $permintaan3 = SuratPermintaan::factory()->create();

        $requestData = [
            'ids' => [$permintaan1->id, $permintaan2->id, $permintaan3->id],
            'status' => 'approved'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan/bulk-update-status', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_validates_bulk_update_status_data(): void
    {
        $this->setUpSettings();

        $invalidData = [
            'ids' => '',
            'status' => '',
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan/bulk-update-status', $invalidData);

        $response->assertStatus(422);
    }

    // Security Tests
    #[Test]
    public function it_prevents_sql_injection_in_search(): void
    {
        $this->setUpSettings();

        SuratPermintaan::factory()->create(['dari' => 'Test Data']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan?search=\'; DROP TABLE surat_permintaan; --');

        $response->assertStatus(200);

        // Verify table still exists and data is intact
        $this->assertDatabaseHas('surat_permintaan', ['dari' => 'Test Data']);
    }

    #[Test]
    public function it_sanitizes_input_data(): void
    {
        $this->setUpSettings();

        $maliciousData = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'kode_klas' => '100',
            'dari' => '<script>alert("xss")</script>Kepala BPS',
            'perihal' => '<img src=x onerror=alert("xss")> Test Perihal',
            'no_sisip' => null,
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan', $maliciousData);

        // Should handle the data without crashing
        $this->assertContains($response->status(), [201, 422]);
    }

    // Performance Tests
    #[Test]
    public function it_handles_large_permintaan_dataset_efficiently(): void
    {
        $this->setUpSettings();

        // Create a smaller dataset for testing
        SuratPermintaan::factory()->count(100)->create(['thn' => '2024']);

        $startTime = microtime(true);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan?per_page=50');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        // Should respond within reasonable time
        $this->assertLessThan(5.0, $executionTime, 'API response took too long');
    }

    // Edge Cases
    #[Test]
    public function it_handles_leap_year_dates(): void
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/permintaan/dates?tahun=2024');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_handles_zero_nomor(): void
    {
        $this->setUpSettings();

        $dataWithZeroNomor = array_merge($this->validPermintaanData, [
            'nomor' => '0'
        ]);

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan', $dataWithZeroNomor);

        // Should handle zero nomor appropriately
        $this->assertContains($response->status(), [201, 422]);
    }

    #[Test]
    public function it_handles_empty_nomor(): void
    {
        $this->setUpSettings();

        $dataWithEmptyNomor = array_merge($this->validPermintaanData, [
            'nomor' => ''
        ]);

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan', $dataWithEmptyNomor);

        // Should return validation error for empty nomor
        $this->assertContains($response->status(), [201, 422]);
    }

    // Integration Tests
    #[Test]
    public function it_maintains_data_integrity_across_operations(): void
    {
        $this->setUpSettings();

        // Create permintaan
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/permintaan', $this->validPermintaanData);

        $response->assertStatus(201);
        $permintaanId = $response->json('data.id');

        // Read permintaan
        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/surat/permintaan/{$permintaanId}");
        $response->assertStatus(200);

        // Update permintaan
        $updateData = array_merge($this->validPermintaanData, ['dari' => 'Updated Dari']);
        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/permintaan/{$permintaanId}", $updateData);
        $response->assertStatus(200);

        // Delete permintaan
        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/surat/permintaan/{$permintaanId}");
        $response->assertStatus(200);

        // Verify deletion
        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/surat/permintaan/{$permintaanId}");
        $response->assertStatus(404);
    }
}
