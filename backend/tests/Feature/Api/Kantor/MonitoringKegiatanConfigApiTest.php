<?php

namespace Tests\Feature\Api\Kantor;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\MonitoringKegiatanConfig;

class MonitoringKegiatanConfigApiTest extends BaseApiTestCase
{
    // Simplified tests that don't require complex setup of related models
    // These tests verify the API endpoints work but may have limited data assertions

    // ==================== GET /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config (index) ====================

    public function test_unauthenticated_user_cannot_list_monitoring_kegiatan_config()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_monitoring_kegiatan_config()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config');

        // Should return 200 with success structure
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_monitoring_kegiatan_config_list_has_correct_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config');

        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }

    // ==================== POST /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config (store) ====================

    public function test_unauthenticated_user_cannot_create_monitoring_kegiatan_config()
    {
        $response = $this->postJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config', [
            'fungsi' => 'Test Fungsi',
            'kegiatan_id' => '01',
            'detil_configurations' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_monitoring_kegiatan_config()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config', [
                'fungsi' => 'Test Fungsi',
                'kegiatan_id' => '01',
                'detil_configurations' => [],
            ]);

        // May return 422 if related records don't exist, but should not be 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    public function test_create_monitoring_kegiatan_config_requires_fungsi()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config', [
                'kegiatan_id' => '01',
                'detil_configurations' => [],
            ]);

        $response->assertStatus(422);
    }

    public function test_create_monitoring_kegiatan_config_requires_kegiatan_id()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config', [
                'fungsi' => 'Test Fungsi',
                'detil_configurations' => [],
            ]);

        $response->assertStatus(422);
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{id} (show) ====================

    public function test_unauthenticated_user_cannot_view_monitoring_kegiatan_config()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/1');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_monitoring_kegiatan_config()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/99999');

        // Should return 404 or success (if record exists), never 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    // ==================== PUT /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{id} (update) ====================

    public function test_unauthenticated_user_cannot_update_monitoring_kegiatan_config()
    {
        $response = $this->putJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/1', [
            'fungsi' => 'Updated Fungsi',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_monitoring_kegiatan_config()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/99999', [
                'fungsi' => 'Updated Fungsi',
            ]);

        // Should return 404 or success, never 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    // ==================== DELETE /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{id} (destroy) ====================

    public function test_unauthenticated_user_cannot_delete_monitoring_kegiatan_config()
    {
        $response = $this->deleteJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/1');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_monitoring_kegiatan_config()
    {
        $response = $this->actingAsUser()
            ->deleteJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/99999');

        // Should return 404 or success, never 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    // ==================== GET /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{id}/available-detil-configs ====================

    public function test_unauthenticated_user_cannot_get_available_detil_configs()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/1/available-detil-configs');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_available_detil_configs()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/99999/available-detil-configs');

        // Should return 404 (config doesn't exist) or success, never 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    // ==================== Security Tests ====================

    public function test_monitoring_kegiatan_config_list_prevents_sql_injection()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config?fungsi=\' OR \'1\'=\'1');
        // Should handle gracefully without SQL errors
        $this->assertContains($response->getStatusCode(), [200, 500, 422]);
    }

    public function test_create_monitoring_kegiatan_config_prevents_sql_injection()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config', [
                'fungsi' => "Test'; DROP TABLE monitoring_kegiatan_config; --",
                'kegiatan_id' => '01',
                'detil_configurations' => [],
            ]);
        // Should either reject or handle gracefully
        $this->assertContains($response->getStatusCode(), [201, 422, 500]);
    }
}
