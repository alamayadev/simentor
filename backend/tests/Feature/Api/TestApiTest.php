<?php

namespace Tests\Feature\Api;

use App\Models\Pegawai;
use App\Models\User;

/**
 * TestApiController Tests
 *
 * NOTE: TestApiController exists but has no routes defined in routes/api.php.
 * These tests document that the endpoints are not accessible (404).
 * The functionality appears to be implemented in PegawaiApiController instead.
 */
class TestApiTest extends BaseApiTestCase
{
    // ==================== GET /api/test/pegawai ====================

    public function test_test_pegawai_endpoint_not_implemented()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/test/pegawai');

        // Endpoint not defined in routes
        $response->assertStatus(404);
    }

    public function test_test_jabatan_pangkat_list_endpoint_not_implemented()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/test/jabatan-pangkat-list');

        // Endpoint not defined in routes
        $response->assertStatus(404);
    }

    // ==================== Documentation ====================

    public function test_test_controller_exists_but_no_routes()
    {
        // Verify the controller exists
        $this->assertFileExists(app_path('Http/Controllers/Api/TestApiController.php'));

        // But has no routes defined
        $response = $this->actingAsUser()
            ->getJson('/api/test/pegawai');

        $this->assertEquals(404, $response->getStatusCode(),
            'TestApiController exists but has no routes defined in routes/api.php');
    }
}
