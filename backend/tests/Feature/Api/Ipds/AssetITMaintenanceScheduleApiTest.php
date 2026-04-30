<?php

namespace Tests\Feature\Api\Ipds;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\User;

/**
 * API Tests for Asset IT Maintenance Schedule Controller
 * 
 * TODO: Implement proper tests for AssetITMaintenanceScheduleApiController
 * This file was a duplicate of TiketApiTest and has been reset.
 */
class AssetITMaintenanceScheduleApiTest extends BaseApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_requires_authentication_to_list_maintenance_schedules()
    {
        $response = $this->getJson('/api/ipds/asset-it-maintenance-schedule');
        
        $response->assertStatus(401);
    }

    /** @test */
    public function it_lists_maintenance_schedules_for_authenticated_user()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/ipds/asset-it-maintenance-schedule');
        
        $response->assertStatus(200);
    }
}
