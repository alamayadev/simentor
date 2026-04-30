<?php

namespace Tests\Feature\Api\Kantor;

use App\Models\Mitra;
use App\Models\Penugasan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Api\BaseApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class SpkApiTest extends BaseApiTestCase
{
    use RefreshDatabase;

    private Penugasan $penugasan;
    private Mitra $testMitra;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testMitra = Mitra::factory()->create();

        // Create test penugasan data
        $this->penugasan = Penugasan::factory()->create([
            'mitra_id' => $this->testMitra->id,
            'bln_bayar' => '2024-01-01',
            'nilai' => 1500000,
            'no_sk' => null,
            'no_bast' => null,
        ]);
    }

    // Authentication Tests for Index
    #[Test]
    public function it_requires_authentication_to_index_spk(): void
    {
        $response = $this->getJson('/api/kantor/spk');

        $response->assertStatus(401);
    }

    // Authentication Tests for Show
    #[Test]
    public function it_requires_authentication_to_show_spk(): void
    {
        $response = $this->getJson("/api/kantor/spk/{$this->testMitra->id}/2024-01");

        $response->assertStatus(401);
    }

    // Authentication Tests for Update
    #[Test]
    public function it_requires_authentication_to_update_spk(): void
    {
        $response = $this->putJson("/api/kantor/spk/{$this->testMitra->id}/2024-01", [
            'no_sk' => 'SK-001/2024'
        ]);

        $response->assertStatus(401);
    }

    // Authentication Tests for Bulk Update SPK
    #[Test]
    public function it_requires_authentication_to_bulk_update_spk(): void
    {
        $response = $this->putJson('/api/kantor/spk/bulk-update', [
            'mitra_ids' => [$this->testMitra->id],
            'tgl_sk' => '2024-01-15'
        ]);

        $response->assertStatus(401);
    }

    // Authentication Tests for Bulk Update BAST
    #[Test]
    public function it_requires_authentication_to_bulk_update_bast(): void
    {
        $response = $this->putJson('/api/kantor/spk/bulk-update-bast', [
            'mitra_ids' => [$this->testMitra->id],
            'tgl_bast' => '2024-01-15'
        ]);

        $response->assertStatus(401);
    }

    // Authentication Tests for Monitoring
    #[Test]
    public function it_requires_authentication_to_monitoring_spk(): void
    {
        $response = $this->getJson('/api/kantor/spk/monitoring?type=tanpa_spk');

        $response->assertStatus(401);
    }

    // Index Method Tests
    #[Test]
    public function it_lists_spk_successfully_as_admin(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create additional test data
        Penugasan::factory()->count(5)->create([
            'bln_bayar' => '2024-02-01'
        ]);

        $response = $this->getJson('/api/kantor/spk');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully',
            ]);

        $this->assertIsArray($response->json('data'));
        $this->assertArrayHasKey('listbln', $response->json());
        $this->assertArrayHasKey('limit_nilai', $response->json());
        $this->assertArrayHasKey('links', $response->json());
        $this->assertArrayHasKey('meta', $response->json());
    }

    #[Test]
    public function it_lists_spk_successfully_as_user(): void
    {
        Sanctum::actingAs($this->regularUser, ['*']);

        $response = $this->getJson('/api/kantor/spk');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully',
            ]);
    }

    #[Test]
    public function it_filters_spk_by_selected_month(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create test data for different months
        Penugasan::factory()->create(['bln_bayar' => '2024-01-01']);
        Penugasan::factory()->create(['bln_bayar' => '2024-02-01']);
        Penugasan::factory()->create(['bln_bayar' => '2024-03-01']);

        $response = $this->getJson('/api/kantor/spk?selectedbln=2024-02');

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $spk) {
            $this->assertEquals('2024-02-01', $spk['bln_bayar']);
        }
    }

    #[Test]
    public function it_filters_spk_by_selected_month_with_full_date(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        Penugasan::factory()->create(['bln_bayar' => '2024-01-01']);
        Penugasan::factory()->create(['bln_bayar' => '2024-02-01']);

        $response = $this->getJson('/api/kantor/spk?selectedbln=2024-02-15');

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $spk) {
            $this->assertEquals('2024-02-01', $spk['bln_bayar']);
        }
    }

    #[Test]
    public function it_sorts_spk_by_different_fields(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        Penugasan::factory()->create(['bln_bayar' => '2024-01-01', 'nilai' => 1000000]);
        Penugasan::factory()->create(['bln_bayar' => '2024-02-01', 'nilai' => 2000000]);

        // Test sort by total (nilai)
        $response = $this->getJson('/api/kantor/spk?sort_by=total&sort_dir=ASC');
        $response->assertStatus(200);

        // Test sort by bln_bayar
        $response = $this->getJson('/api/kantor/spk?sort_by=bln_bayar&sort_dir=DESC');
        $response->assertStatus(200);

        // Test sort by jml_tugas
        $response = $this->getJson('/api/kantor/spk?sort_by=jml_tugas&sort_dir=DESC');
        $response->assertStatus(200);

        // Test sort by id
        $response = $this->getJson('/api/kantor/spk?sort_by=id&sort_dir=ASC');
        $response->assertStatus(200);
    }

    #[Test]
    public function it_paginates_spk_correctly(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create 25 penugasan with unique mitra_id AND unique bln_bayar to ensure proper grouping
        // Each penugasan must have unique (mitra_id, bln_bayar) combination for the groupBy to work
        for ($i = 0; $i < 25; $i++) {
            $mitra = Mitra::factory()->create();
            // Use unique month and day combinations
            $month = str_pad(floor($i / 10) + 1, 2, '0', STR_PAD_LEFT);
            $day = str_pad(($i % 10) + 1, 2, '0', STR_PAD_LEFT);
            Penugasan::factory()->create([
                'mitra_id' => $mitra->id,
                'bln_bayar' => "2024-{$month}-{$day}",
            ]);
        }

        $response = $this->getJson('/api/kantor/spk?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.count', 10)
            ->assertJsonPath('pagination_info.total_records', 26) // 25 created + 1 from setup
            ->assertJsonPath('pagination_info.total_page', 3);

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNextPage($response);
        $this->assertCount(10, $response->json('data'));
    }

    #[Test]
    public function it_validates_index_parameters(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Test invalid sort_by
        $response = $this->getJson('/api/kantor/spk?sort_by=invalid_field');
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error',
            ]);

        // Test invalid sort_dir
        $response = $this->getJson('/api/kantor/spk?sort_dir=INVALID');
        $response->assertStatus(422);

        // Test invalid per_page
        $response = $this->getJson('/api/kantor/spk?per_page=0');
        $response->assertStatus(422);

        // Test per_page too high
        $response = $this->getJson('/api/kantor/spk?per_page=200');
        $response->assertStatus(422);
    }

    // Show Method Tests
    #[Test]
    public function it_shows_spk_successfully_as_admin(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson("/api/kantor/spk/{$this->testMitra->id}/2024-01");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully',
                'data' => [
                    'penugasan_mitra' => [
                        [
                            'mitra_id' => $this->testMitra->id,
                            'bln_bayar' => '2024-01-01',
                        ]
                    ]
                ],
            ]);
    }

    #[Test]
    public function it_shows_spk_successfully_as_user(): void
    {
        Sanctum::actingAs($this->regularUser, ['*']);

        $response = $this->getJson("/api/kantor/spk/{$this->testMitra->id}/2024-01");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully',
            ]);
    }

    #[Test]
    public function it_returns_not_found_for_nonexistent_spk(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson('/api/kantor/spk/999/2024-99');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'SPK record not found',
            ]);
    }

    // Update Method Tests
    #[Test]
    public function it_updates_spk_successfully_as_admin(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $updateData = [
            'no_sk' => 'SK-001/2024',
            'tgl_sk' => '2024-01-15',
            'no_bast' => 'BAST-001/2024',
            'tgl_bast' => '2024-01-31',
        ];

        $response = $this->putJson("/api/kantor/spk/{$this->testMitra->id}/2024-01", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'SPK record updated successfully',
            ]);

        $this->assertDatabaseHas('penugasan', [
            'mitra_id' => $this->testMitra->id,
            'bln_bayar' => '2024-01-01',
            'no_sk' => 'SK-001/2024',
            'tgl_sk' => '2024-01-15',
            'no_bast' => 'BAST-001/2024',
            'tgl_bast' => '2024-01-31',
        ]);
    }

    #[Test]
    public function it_updates_spk_partially(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $partialUpdate = [
            'no_sk' => 'SK-PARTIAL/2024',
        ];

        $response = $this->putJson("/api/kantor/spk/{$this->testMitra->id}/2024-01", $partialUpdate);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'SPK record updated successfully',
            ]);

        $this->assertDatabaseHas('penugasan', [
            'mitra_id' => $this->testMitra->id,
            'bln_bayar' => '2024-01-01',
            'no_sk' => 'SK-PARTIAL/2024',
        ]);
    }

    #[Test]
    public function it_returns_not_found_when_updating_nonexistent_spk(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->putJson('/api/kantor/spk/999/2024-99', [
            'no_sk' => 'SK-001/2024'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No SPK records found to update',
            ]);
    }

    #[Test]
    public function it_validates_update_data(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $invalidData = [
            'no_sk' => str_repeat('a', 300), // Too long
            'tgl_sk' => 'invalid-date',
            'no_bast' => str_repeat('b', 300), // Too long
            'tgl_bast' => 'invalid-date',
        ];

        $response = $this->putJson("/api/kantor/spk/{$this->testMitra->id}/2024-01", $invalidData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error',
            ]);
    }

    // Bulk Update SPK Tests
    #[Test]
    public function it_bulk_updates_spk_successfully_as_admin(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create multiple mitra and penugasan
        $mitra2 = Mitra::factory()->create();
        Penugasan::factory()->create([
            'mitra_id' => $mitra2->id,
            'bln_bayar' => '2024-01-01',
            'no_sk' => null,
            'no_bast' => null
        ]);

        $requestData = [
            'mitra_ids' => [$this->testMitra->id, $mitra2->id],
            'tgl_sk' => '2024-01-15',
            'bln_bayar' => '2024-01',
        ];



        $response = $this->putJson('/api/kantor/spk/bulk-update', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'SPK records updated successfully',
                'data' => [
                    'updated_count' => 2,
                ],
            ]);

        // Check that records were updated with sequential numbering
        $this->assertDatabaseHas('penugasan', [
            'mitra_id' => $this->testMitra->id,
            'no_sk' => '0001',
            'tgl_sk' => '2024-01-01', // Should be first of month
            'jangka_waktu_mulai' => '2024-01-01',
        ]);
    }

    #[Test]
    public function it_bulk_updates_spk_successfully_as_user(): void
    {
        Sanctum::actingAs($this->regularUser, ['*']);

        $requestData = [
            'mitra_ids' => [$this->testMitra->id],
            'tgl_sk' => '2024-01-15',
            'bln_bayar' => '2024-01',
        ];

        $response = $this->putJson('/api/kantor/spk/bulk-update', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'SPK records updated successfully',
            ]);
    }

    #[Test]
    public function it_validates_bulk_update_spk_data(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Test missing mitra_ids
        $response = $this->putJson('/api/kantor/spk/bulk-update', [
            'tgl_sk' => '2024-01-15'
        ]);
        $response->assertStatus(422);

        // Test missing tgl_sk
        $response = $this->putJson('/api/kantor/spk/bulk-update', [
            'mitra_ids' => [$this->testMitra->id]
        ]);
        $response->assertStatus(422);

        // Test invalid mitra_id
        $response = $this->putJson('/api/kantor/spk/bulk-update', [
            'mitra_ids' => [999],
            'tgl_sk' => '2024-01-15'
        ]);
        $response->assertStatus(422);
    }

    // Bulk Update BAST Tests
    #[Test]
    public function it_bulk_updates_bast_successfully_as_admin(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Update penugasan with no_sk
        $this->penugasan->update([
            'no_sk' => '0001',
            'no_bast' => null
        ]);

        $requestData = [
            'mitra_ids' => [$this->testMitra->id],
            'tgl_bast' => '2024-01-20',
            'bln_bayar' => '2024-01',
        ];

        $response = $this->putJson('/api/kantor/spk/bulk-update-bast', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'BAST records updated successfully',
                'data' => [
                    'updated_count' => 1,
                ],
            ]);

        $this->assertDatabaseHas('penugasan', [
            'mitra_id' => $this->testMitra->id,
            'no_bast' => '0001',
            'tgl_bast' => '2024-01-31', // Should be last of month
            'jangka_waktu_selesai' => '2024-01-31',
        ]);
    }

    #[Test]
    public function it_validates_bulk_update_bast_data(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Test missing mitra_ids
        $response = $this->putJson('/api/kantor/spk/bulk-update-bast', [
            'tgl_bast' => '2024-01-15'
        ]);
        $response->assertStatus(422);

        // Test missing tgl_bast
        $response = $this->putJson('/api/kantor/spk/bulk-update-bast', [
            'mitra_ids' => [$this->testMitra->id]
        ]);
        $response->assertStatus(422);
    }

    // Monitoring Tests
    #[Test]
    public function it_gets_tanpa_spk_monitoring_data(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create penugasan without SK or BAST
        Penugasan::factory()->create([
            'no_sk' => null,
            'no_bast' => null
        ]);

        $response = $this->getJson('/api/kantor/spk/monitoring?type=tanpa_spk');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Monitoring data retrieved',
            ]);

        $this->assertIsArray($response->json('data'));
        $this->assertArrayHasKey('listbln', $response->json());
    }

    #[Test]
    public function it_gets_tanpa_bast_monitoring_data(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create penugasan with SK but without BAST
        Penugasan::factory()->create([
            'no_sk' => 'SK-001',
            'no_bast' => null
        ]);

        $response = $this->getJson('/api/kantor/spk/monitoring?type=tanpa_bast');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Monitoring data retrieved',
            ]);
    }

    #[Test]
    public function it_gets_diatas_4jt_monitoring_data(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create penugasan with high value (> 4,000,000)
        Penugasan::factory()->create(['nilai' => 5000000]);

        $response = $this->getJson('/api/kantor/spk/monitoring?type=diatas_4jt');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Monitoring data retrieved',
            ]);
    }

    #[Test]
    public function it_validates_monitoring_parameters(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Test invalid type
        $response = $this->getJson('/api/kantor/spk/monitoring?type=invalid_type');
        $response->assertStatus(422);

        // Test missing type
        $response = $this->getJson('/api/kantor/spk/monitoring');
        $response->assertStatus(422);

        // Test invalid per_page
        $response = $this->getJson('/api/kantor/spk/monitoring?type=tanpa_spk&per_page=0');
        $response->assertStatus(422);
    }

    #[Test]
    public function it_paginates_monitoring_data(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create multiple records
        Penugasan::factory()->count(15)->create([
            'no_sk' => null,
            'no_bast' => null
        ]);

        $response = $this->getJson('/api/kantor/spk/monitoring?type=tanpa_spk&per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.count', 10);

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNextPage($response);
    }

    // Security Tests
    #[Test]
    public function it_prevents_sql_injection_in_monitoring_type(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $p = Penugasan::factory()->create();

        $response = $this->getJson('/api/kantor/spk/monitoring?type=\'; DROP TABLE penugasans; --');

        $response->assertStatus(422); // Should be caught by validation

        // Verify table still exists
        $this->assertDatabaseHas('penugasan', ['id' => $p->id]);
    }

    #[Test]
    public function it_prevents_sql_injection_in_sort_parameters(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $p = Penugasan::factory()->create();

        $response = $this->getJson('/api/kantor/spk?sort_by=id;--&sort_dir=ASC');

        $response->assertStatus(422); // Should be caught by validation

        // Verify data is intact
        $this->assertDatabaseHas('penugasan', ['id' => $p->id]);
    }

    // Performance Tests
    #[Test]
    public function it_handles_large_spk_dataset_efficiently(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create a large dataset
        Penugasan::factory()->count(1000)->create();

        $startTime = microtime(true);

        $response = $this->getJson('/api/kantor/spk?per_page=50');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertCount(50, $response->json('data'));

        // Should respond within reasonable time (adjust threshold as needed)
        $this->assertLessThan(3.0, $executionTime, 'API response took too long');
    }

    // Edge Cases
    #[Test]
    public function it_handles_leap_year_dates(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        Penugasan::factory()->create(['bln_bayar' => '2024-02-01']); // Leap year

        $response = $this->getJson('/api/kantor/spk/monitoring?type=tanpa_spk&selectedbln=2024-02');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_handles_year_boundary(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create data across year boundary
        Penugasan::factory()->create(['bln_bayar' => '2023-12-01', 'no_sk' => null, 'no_bast' => null]);
        Penugasan::factory()->create(['bln_bayar' => '2024-01-01', 'no_sk' => null, 'no_bast' => null]);

        $response = $this->getJson('/api/kantor/spk/monitoring?type=tanpa_spk&selectedbln=2024-01');

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $record) {
            $this->assertEquals('2024-01-01', $record['bln_bayar']);
        }
    }

    #[Test]
    public function it_handles_empty_monitoring_results(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Don't create any data that matches monitoring criteria

        $response = $this->getJson('/api/kantor/spk/monitoring?type=tanpa_spk');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Monitoring data retrieved',
                'data' => [],
            ]);
    }

    #[Test]
    public function it_handles_null_values_in_bulk_update(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $this->penugasan->update([
            'no_sk' => 'EXISTING-SK',
            'no_bast' => null
        ]);

        // Update should only affect records with null no_sk
        $requestData = [
            'mitra_ids' => [$this->testMitra->id],
            'tgl_sk' => '2024-01-15',
            'bln_bayar' => '2024-01',
        ];

        $response = $this->putJson('/api/kantor/spk/bulk-update', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'updated_count' => 0, // Should be 0 because no_sk is not null
                ],
            ]);

        // Verify existing SK was not changed
        $this->assertDatabaseHas('penugasan', [
            'mitra_id' => $this->testMitra->id,
            'no_sk' => 'EXISTING-SK',
        ]);
    }

    // Integration Tests
    #[Test]
    public function it_maintains_data_integrity_across_operations(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create initial data
        $penugasan = Penugasan::factory()->create([
            'mitra_id' => $this->testMitra->id,
            'bln_bayar' => '2024-01-01',
            'no_sk' => null,
            'no_bast' => null
        ]);

        // Read data
        $response = $this->getJson("/api/kantor/spk/{$this->testMitra->id}/2024-01");
        $response->assertStatus(200);

        // Update SPK
        $updateData = ['no_sk' => 'SK-001/2024', 'tgl_sk' => '2024-01-15'];
        $this->putJson("/api/kantor/spk/{$this->testMitra->id}/2024-01", $updateData);
        $response->assertStatus(200);

        // Verify SPK update
        $response = $this->getJson("/api/kantor/spk/{$this->testMitra->id}/2024-01");
        $data = $response->json('data.penugasan_mitra');
        $this->assertEquals('SK-001/2024', $data[0]['no_sk']);

        // Update BAST
        $bastUpdateData = ['no_bast' => 'BAST-001/2024', 'tgl_bast' => '2024-01-31'];
        $this->putJson("/api/kantor/spk/{$this->testMitra->id}/2024-01", $bastUpdateData);
        $response->assertStatus(200);

        // Verify BAST update
        $response = $this->getJson("/api/kantor/spk/{$this->testMitra->id}/2024-01");
        $data = $response->json('data.penugasan_mitra');
        $this->assertEquals('BAST-001/2024', $data[0]['no_bast']);

        // Verify monitoring reflects updated state
        $response = $this->getJson('/api/kantor/spk/monitoring?type=tanpa_spk');
        $data = $response->json('data');

        // Should not include our record since it now has both SK and BAST
        foreach ($data as $record) {
            $this->assertNotEquals($this->testMitra->id, $record['mitra_id']);
        }
    }

    #[Test]
    public function it_handles_concurrent_bulk_updates(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $mitra2 = Mitra::factory()->create();
        Penugasan::factory()->create([
            'mitra_id' => $mitra2->id,
            'bln_bayar' => '2024-01-01',
            'no_sk' => null,
            'no_bast' => null
        ]);

        // First bulk update
        $requestData1 = [
            'mitra_ids' => [$this->testMitra->id],
            'tgl_sk' => '2024-01-15',
            'bln_bayar' => '2024-01',
        ];

        $response1 = $this->putJson('/api/kantor/spk/bulk-update', $requestData1);
        $response1->assertStatus(200);

        // Second bulk update
        $requestData2 = [
            'mitra_ids' => [$mitra2->id],
            'tgl_sk' => '2024-01-15',
            'bln_bayar' => '2024-01',
        ];

        $response2 = $this->putJson('/api/kantor/spk/bulk-update', $requestData2);
        $response2->assertStatus(200);

        // Verify sequential numbering
        $this->assertDatabaseHas('penugasan', [
            'mitra_id' => $this->testMitra->id,
            'no_sk' => '0001',
        ]);

        $this->assertDatabaseHas('penugasan', [
            'mitra_id' => $mitra2->id,
            'no_sk' => '0002',
        ]);
    }
}
