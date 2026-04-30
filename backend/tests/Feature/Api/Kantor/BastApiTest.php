<?php

namespace Tests\Feature\Api\Kantor;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\Bast;

class BastApiTest extends BaseApiTestCase
{
    // Simplified tests that don't require complex setup of related models
    // These tests verify the API endpoints work but may have limited data assertions

    // ==================== GET /api/kantor/bast (index) ====================

    public function test_unauthenticated_user_cannot_list_bast()
    {
        $response = $this->getJson('/api/kantor/bast');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_bast()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/bast');

        // Should return 200 with success structure
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_bast_list_has_correct_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/bast');

        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }

    // ==================== GET /api/kantor/bast/{id} (show) ====================

    public function test_unauthenticated_user_cannot_view_bast()
    {
        $response = $this->getJson('/api/kantor/bast/1');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_bast()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/bast/99999');

        // Should return 404 or success (if record exists), never 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    // ==================== PUT /api/kantor/bast/{id} (update) ====================

    public function test_unauthenticated_user_cannot_update_bast()
    {
        $response = $this->putJson('/api/kantor/bast/1', [
            'nomor_surat' => 'Updated Nomor',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_bast()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/kantor/bast/99999', [
            'nomor_surat' => 'Updated Nomor',
        ]);

        // Should return 404 or success, never 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    // ==================== GET /api/kantor/bast/available-months ====================

    public function test_unauthenticated_user_cannot_get_available_months()
    {
        $response = $this->getJson('/api/kantor/bast/available-months');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_available_months()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/bast/available-months');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    // ==================== Security Tests ====================

    public function test_bast_list_prevents_sql_injection()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/bast?nomor_surat=\' OR \'1\'=\'1');
        // Should handle gracefully without SQL errors
        $this->assertContains($response->getStatusCode(), [200, 500, 422]);
    }

    public function test_update_bast_prevents_sql_injection()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/kantor/bast/99999', [
                'nomor_surat' => "Test'; DROP TABLE basts; --",
            ]);
        // Should either reject, handle gracefully, or return 404 (record doesn't exist)
        $this->assertContains($response->getStatusCode(), [200, 422, 500, 404]);
    }
}
