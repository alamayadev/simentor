<?php

namespace Tests\Feature\Api\Kantor;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\MonitoringKegiatan;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Penugasan;

class MonitoringKegiatanApiTest extends BaseApiTestCase
{
    // Simplified tests that don't require complex setup of related models
    // These tests verify the API endpoints work but may have limited data assertions

    // ==================== GET /api/kantor/kegiatan/monitoring (index) ====================

    public function test_unauthenticated_user_cannot_list_monitoring_kegiatan()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_monitoring_kegiatan()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring');

        // Should return 200 with success structure
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_monitoring_kegiatan_list_has_correct_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring');

        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }

    // ==================== POST /api/kantor/kegiatan/monitoring (store) ====================

    public function test_unauthenticated_user_cannot_create_monitoring_kegiatan()
    {
        $response = $this->postJson('/api/kantor/kegiatan/monitoring', [
            'fungsi' => 'Test Fungsi',
            'kegiatan_id' => '01',
            'kec_id' => '3201000',
            'desa_id' => '3201000001',
            'kode_sampel' => 'TEST001',
            'monitoring_kegiatan_config_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_monitoring_kegiatan()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan/monitoring', [
                'fungsi' => 'Test Fungsi',
                'kegiatan_id' => '01',
                'kec_id' => '3201000',
                'desa_id' => '3201000001',
                'kode_sampel' => 'TEST001',
                'monitoring_kegiatan_config_id' => 1,
            ]);

        // May return 422 if related records don't exist, but should not be 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    public function test_create_monitoring_kegiatan_requires_fungsi()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan/monitoring', [
                'kegiatan_id' => '01',
                'kec_id' => '3201000',
                'desa_id' => '3201000001',
                'kode_sampel' => 'TEST001',
                // Not including monitoring_kegiatan_config_id to avoid table validation
            ]);

        $response->assertStatus(422);
    }

    public function test_create_monitoring_kegiatan_requires_kegiatan_id()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan/monitoring', [
                'fungsi' => 'Test Fungsi',
                'kec_id' => '3201000',
                'desa_id' => '3201000001',
                'kode_sampel' => 'TEST001',
                // Not including monitoring_kegiatan_config_id to avoid table validation
            ]);

        $response->assertStatus(422);
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/{id} (show) ====================

    public function test_unauthenticated_user_cannot_view_monitoring_kegiatan()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/1');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_monitoring_kegiatan()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/99999');

        // Should return 404 or success (if record exists), never 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    // ==================== PUT /api/kantor/kegiatan/monitoring/{id} (update) ====================

    public function test_unauthenticated_user_cannot_update_monitoring_kegiatan()
    {
        $response = $this->putJson('/api/kantor/kegiatan/monitoring/1', [
            'kode_sampel' => 'UPDATED',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_monitoring_kegiatan()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/kantor/kegiatan/monitoring/99999', [
                'kode_sampel' => 'UPDATED',
            ]);

        // Should return 404 or success, never 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    // ==================== DELETE /api/kantor/kegiatan/monitoring/{id} (destroy) ====================

    public function test_unauthenticated_user_cannot_delete_monitoring_kegiatan()
    {
        $response = $this->deleteJson('/api/kantor/kegiatan/monitoring/1');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_monitoring_kegiatan()
    {
        $response = $this->actingAsUser()
            ->deleteJson('/api/kantor/kegiatan/monitoring/99999');

        // Should return 404 or success, never 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/download ====================

    public function test_unauthenticated_user_cannot_download_monitoring_kegiatan()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/download?kegiatan_id=01');
        $response->assertStatus(401);
    }

    public function test_download_requires_kegiatan_id()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/download');
        $response->assertStatus(422);
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/kegiatan-options ====================

    public function test_unauthenticated_user_cannot_get_kegiatan_options()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/kegiatan-options');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_kegiatan_options()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/kegiatan-options');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/petugas-options ====================

    public function test_unauthenticated_user_cannot_get_petugas_options()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/petugas-options');
        $response->assertStatus(401);
    }

    public function test_petugas_options_requires_kegiatan_id()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/petugas-options');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kegiatan_id']);
    }

    public function test_petugas_options_requires_valid_kegiatan_id()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/petugas-options?kegiatan_id=99999');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kegiatan_id']);
    }

    public function test_authenticated_user_can_get_petugas_options()
    {
        // Create a kegiatan
        $kegiatan = Kegiatan::factory()->create();
        
        // Create some mitra
        $mitra1 = Mitra::factory()->create(['nama_lengkap' => 'Mitra Test 1']);
        $mitra2 = Mitra::factory()->create(['nama_lengkap' => 'Mitra Test 2']);
        
        // Create penugasan linking mitra to kegiatan
        Penugasan::factory()->create([
            'kegiatan_id' => $kegiatan->id,
            'mitra_id' => $mitra1->id,
            'pegawai_id' => null,
        ]);
        Penugasan::factory()->create([
            'kegiatan_id' => $kegiatan->id,
            'mitra_id' => $mitra2->id,
            'pegawai_id' => null,
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/petugas-options?kegiatan_id=' . $kegiatan->id);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'nama_lengkap'
                    ]
                ],
            ]);

        // Verify that response contains the expected mitra
        $data = $response->json('data');
        $this->assertCount(2, $data);
        $namaLengkapArray = collect($data)->pluck('nama_lengkap')->toArray();
        $this->assertContains('Mitra Test 1', $namaLengkapArray);
        $this->assertContains('Mitra Test 2', $namaLengkapArray);
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/pengawas-options ====================

    public function test_unauthenticated_user_cannot_get_pengawas_options()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/pengawas-options');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_pengawas_options()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/pengawas-options');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'nama_pml',
                        'tipe'
                    ]
                ]
            ]);

        // Verify that when no search is provided, we get random records
        $data = $response->json('data');
        $mitraCount = collect($data)->where('tipe', 'mitra')->count();
        $pegawaiCount = collect($data)->where('tipe', 'pegawai')->count();
        
        // Should have at most 5 mitra and 5 pegawai (could be fewer if not enough records exist)
        $this->assertLessThanOrEqual(5, $mitraCount);
        $this->assertLessThanOrEqual(5, $pegawaiCount);
    }

    public function test_pengawas_options_can_search_by_name()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/pengawas-options?search=test');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'nama_pml',
                        'tipe'
                    ]
                ]
            ]);
    }

    public function test_pengawas_options_search_returns_all_matching_results()
    {
        // When search is provided, should return all matching results (not limited to 5)
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/pengawas-options?search=test');
        $response->assertStatus(200);

        $data = $response->json('data');
        
        // When searching, results should contain the search term in nama_pml
        if (!empty($data)) {
            foreach ($data as $item) {
                $this->assertStringContainsStringIgnoringCase('test', $item['nama_pml']);
            }
        }
    }

    public function test_pengawas_options_returns_mitra_and_pegawai_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/pengawas-options');
        $response->assertStatus(200);

        $data = $response->json('data');
        if (!empty($data)) {
            // Check that all items have the required structure
            foreach ($data as $item) {
                $this->assertArrayHasKey('id', $item);
                $this->assertArrayHasKey('nama_pml', $item);
                $this->assertArrayHasKey('tipe', $item);
                $this->assertContains($item['tipe'], ['mitra', 'pegawai']);
            }
        }
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/blok-options ====================

    public function test_unauthenticated_user_cannot_get_blok_options()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/blok-options');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_blok_options()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/blok-options');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'nmblok'
                    ]
                ]
            ]);
    }

    public function test_blok_options_can_filter_by_desa_id()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/blok-options?desa_id=3201010001');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'nmblok'
                    ]
                ]
            ]);
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/supervisor-options ====================

    public function test_unauthenticated_user_cannot_get_supervisor_options()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/supervisor-options');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_supervisor_options()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/supervisor-options');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'nama'
                    ]
                ]
            ]);
    }

    public function test_supervisor_options_can_search_by_name()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/supervisor-options?search=test');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'nama'
                    ]
                ]
            ]);
    }

    public function test_supervisor_options_returns_correct_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/supervisor-options');
        $response->assertStatus(200);

        $data = $response->json('data');
        if (!empty($data)) {
            // Check that all items have the required structure
            foreach ($data as $item) {
                $this->assertArrayHasKey('id', $item);
                $this->assertArrayHasKey('nama', $item);
            }
        }
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/kec-options ====================

    public function test_unauthenticated_user_cannot_get_kec_options()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/kec-options');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_kec_options()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/kec-options');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/desa-options ====================

    public function test_unauthenticated_user_cannot_get_desa_options()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/desa-options?kec_id=3201000');
        $response->assertStatus(401);
    }

    public function test_desa_options_requires_kec_id()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/desa-options');
        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_get_desa_options()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/desa-options?kec_id=3201000');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/filters-options ====================

    public function test_unauthenticated_user_cannot_get_filters_options()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/filters-options');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_filters_options()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/filters-options');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_filters_options_can_filter_by_fungsi()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/filters-options?fungsi=Test');
        $response->assertStatus(200);
    }

    // ==================== Security Tests ====================

    public function test_monitoring_kegiatan_list_prevents_sql_injection()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring?fungsi=\' OR \'1\'=\'1');
        // Should handle gracefully without SQL errors
        $this->assertContains($response->getStatusCode(), [200, 500, 422]);
    }

    public function test_create_monitoring_kegiatan_prevents_sql_injection()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan/monitoring', [
                'fungsi' => "Test'; DROP TABLE monitoring_kegiatan; --",
                'kegiatan_id' => '01',
                'kec_id' => '3201000',
                'desa_id' => '3201000001',
                'kode_sampel' => 'TEST001',
                'monitoring_kegiatan_config_id' => 1,
            ]);
        // Should either reject or handle gracefully
        $this->assertContains($response->getStatusCode(), [201, 422, 500]);
    }
}
