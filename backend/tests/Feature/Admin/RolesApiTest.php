<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RolesApiTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $regularRole = Role::firstOrCreate(['name' => 'regular-user']);

        // Create users
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superAdminRole);

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole($regularRole);
    }

    public function test_super_admin_can_list_roles_excluding_id_1()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/admin/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'guard_name',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);

        $this->assertUnifiedPaginationStructure($response);

        // Verify that role with ID=1 is not included in the response
        $responseData = $response->json();
        $roleIds = collect($responseData['data'])->pluck('id')->toArray();

        $this->assertNotContains(1, $roleIds);
        $this->assertContains(2, $roleIds); // The regular-user role should be present
    }

    public function test_regular_user_cannot_list_roles()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/admin/roles');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden'
            ]);
    }

    public function test_unauthenticated_user_cannot_list_roles()
    {
        $response = $this->getJson('/api/admin/roles');

        // BaseApiController returns 401 with standard error format
        $response->assertStatus(401);
    }
}
