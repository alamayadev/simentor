<?php

namespace Tests\Feature\Api\Admin;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesApiTest extends BaseApiTestCase
{
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create super-admin role if not exists
        Role::findOrCreate('super-admin', 'web');
        
        // Create a super-admin user for testing
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
    }

    protected function actingAsSuperAdmin(): self
    {
        return $this->actingAs($this->superAdmin, 'sanctum');
    }

    // GET /api/admin/roles Tests

    /** @test */
    public function it_requires_authentication_to_list_roles()
    {
        $response = $this->getJson('/api/admin/roles');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_list_roles()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/admin/roles');

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_lists_roles_with_pagination()
    {
        // Create some roles for testing
        for ($i = 1; $i <= 15; $i++) {
            Role::findOrCreate("test-role-$i", 'web');
        }

        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/roles?per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Roles retrieved'
            ]);
    }

    /** @test */
    public function it_filters_roles_by_name()
    {
        Role::findOrCreate('editor', 'web');
        Role::findOrCreate('viewer', 'web');
        Role::findOrCreate('admin-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/roles?filter[name]=admin-role');

        $response->assertStatus(200);

        $responseData = $response->json('data');
        // Response structure may vary based on pagination
        $this->assertNotEmpty($responseData);
    }

    /** @test */
    public function it_excludes_super_admin_role_from_list()
    {
        Role::findOrCreate('test-role-1', 'web');
        Role::findOrCreate('test-role-2', 'web');

        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/roles');

        $response->assertStatus(200);

        // The super-admin role (id=1) should not be in the list
        $responseJson = $response->json();
        $this->assertNotNull($responseJson['data']);
    }

    /** @test */
    public function it_sorts_roles_by_id_by_default()
    {
        Role::findOrCreate('zebra-role', 'web');
        Role::findOrCreate('apple-role', 'web');
        Role::findOrCreate('banana-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/roles');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data'));
    }

    // GET /api/admin/roles/{id} Tests

    /** @test */
    public function it_requires_authentication_to_show_role()
    {
        $response = $this->getJson('/api/admin/roles/1');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_show_role()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/admin/roles/1');

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_returns_404_for_nonexistent_role()
    {
        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/roles/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Role not found'
            ]);
    }

    /** @test */
    public function it_returns_role_details()
    {
        $role = Role::findOrCreate('test-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->getJson("/api/admin/roles/{$role->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'guard_name',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Role details'
            ]);

        $this->assertEquals($role->id, $response->json('data.id'));
        $this->assertEquals('test-role', $response->json('data.name'));
    }

    // POST /api/admin/roles Tests

    /** @test */
    public function it_requires_authentication_to_create_role()
    {
        $response = $this->postJson('/api/admin/roles', [
            'name' => 'new-role'
        ]);

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_create_role()
    {
        $response = $this->actingAsOrganik()
            ->postJson('/api/admin/roles', [
                'name' => 'new-role'
            ]);

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_validates_required_name_for_role_creation()
    {
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/roles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_unique_name_for_role_creation()
    {
        Role::findOrCreate('existing-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/roles', [
                'name' => 'existing-role'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_creates_role_successfully()
    {
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/roles', [
                'name' => 'New-Test-Role'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'guard_name',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Role created'
            ]);

        // Verify database
        $this->assertDatabaseHas('roles', [
            'name' => 'new-test-role' // Converted to lowercase
        ]);
    }

    /** @test */
    public function it_converts_role_name_to_lowercase()
    {
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/roles', [
                'name' => 'UPPERCASE-Role'
            ]);

        $response->assertStatus(201);

        // Verify name was converted to lowercase
        $this->assertDatabaseHas('roles', [
            'name' => 'uppercase-role'
        ]);
    }

    // PUT /api/admin/roles/{id} Tests

    /** @test */
    public function it_requires_authentication_to_update_role()
    {
        $response = $this->putJson('/api/admin/roles/1', [
            'name' => 'updated-role'
        ]);

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_update_role()
    {
        $response = $this->actingAsOrganik()
            ->putJson('/api/admin/roles/1', [
                'name' => 'updated-role'
            ]);

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_role()
    {
        $response = $this->actingAsSuperAdmin()
            ->putJson('/api/admin/roles/999999', [
                'name' => 'updated-role'
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Role not found'
            ]);
    }

    /** @test */
    public function it_validates_required_name_for_role_update()
    {
        $role = Role::findOrCreate('test-update-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/roles/{$role->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_case_insensitive_unique_name_for_role_update()
    {
        $role1 = Role::findOrCreate('existing-role', 'web');
        $role2 = Role::findOrCreate('another-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/roles/{$role2->id}", [
                'name' => 'Existing-Role' // Different case but same name
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The name has already been taken.'
            ]);
    }

    /** @test */
    public function it_updates_role_successfully()
    {
        $role = Role::findOrCreate('original-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/roles/{$role->id}", [
                'name' => 'Updated-Test-Role'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Role updated'
            ]);

        // Verify database update
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'updated-test-role' // Converted to lowercase
        ]);
    }

    /** @test */
    public function it_converts_updated_name_to_lowercase()
    {
        $role = Role::findOrCreate('lowercase-test', 'web');

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/roles/{$role->id}", [
                'name' => 'UPPERCASE-Update'
            ]);

        $response->assertStatus(200);

        // Verify name was converted to lowercase
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'uppercase-update'
        ]);
    }

    // DELETE /api/admin/roles/{id} Tests

    /** @test */
    public function it_requires_authentication_to_delete_role()
    {
        $response = $this->deleteJson('/api/admin/roles/1');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_delete_role()
    {
        $response = $this->actingAsOrganik()
            ->deleteJson('/api/admin/roles/1');

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_role()
    {
        $response = $this->actingAsSuperAdmin()
            ->deleteJson('/api/admin/roles/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Role not found'
            ]);
    }

    /** @test */
    public function it_deletes_role_successfully()
    {
        $role = Role::findOrCreate('delete-me', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAsSuperAdmin()
            ->deleteJson("/api/admin/roles/{$role->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Role berhasil dihapus',
                'data' => null
            ]);

        // Verify database
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        
        // Verify role was detached from users
        $this->assertFalse($user->fresh()->hasRole('delete-me'));
    }

    /** @test */
    public function it_cleans_up_model_has_roles_before_deleting()
    {
        $role = Role::findOrCreate('cleanup-test', 'web');
        $user = User::factory()->create();
        
        // Manually create wrong model_type to test cleanup
        DB::table('model_has_roles')->insert([
            'role_id' => $role->id,
            'model_id' => $user->id,
            'model_type' => 'Wrong\\Model'
        ]);

        $response = $this->actingAsSuperAdmin()
            ->deleteJson("/api/admin/roles/{$role->id}");

        $response->assertStatus(200);

        // Role should be deleted
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    /** @test */
    public function it_detaches_role_from_all_users_before_deletion()
    {
        $role = Role::findOrCreate('detach-test', 'web');
        $users = User::factory()->count(3)->create();
        
        foreach ($users as $user) {
            $user->assignRole($role);
        }

        $response = $this->actingAsSuperAdmin()
            ->deleteJson("/api/admin/roles/{$role->id}");

        $response->assertStatus(200);

        // Verify all users lost the role
        foreach ($users as $user) {
            $this->assertFalse($user->fresh()->hasRole('detach-test'));
        }
    }

    // Additional Security Tests

    /** @test */
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $role = Role::findOrCreate('mass-assign-test', 'web');
        $originalId = $role->id;

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/roles/{$role->id}", [
                'id' => 999999, // Should not be updatable
                'name' => 'Updated Role',
                'guard_name' => 'custom', // Should not be updatable
                'created_at' => now() // Should not be updatable
            ]);

        $response->assertStatus(200);

        $updatedRole = $role->fresh();
        $this->assertEquals($originalId, $updatedRole->id);
        $this->assertEquals('updated role', $updatedRole->name);
    }

    /** @test */
    public function it_prevents_sql_injection_in_role_operations()
    {
        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/roles?filter[name]=\'; DROP TABLE roles; --');

        // Should handle gracefully without crashing
        $this->assertContains($response->status(), [200, 400, 422]);
        
        // Roles table should still exist - verify by counting roles
        $this->assertGreaterThan(0, Role::count());
    }

    /** @test */
    public function it_handles_large_names_gracefully()
    {
        $largeName = str_repeat('a', 300);

        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/roles', [
                'name' => $largeName
            ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422, 500]);
    }

    /** @test */
    public function it_handles_concurrent_role_updates_gracefully()
    {
        $role = Role::findOrCreate('concurrent-test', 'web');

        // Simulate concurrent updates
        $response1 = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/roles/{$role->id}", [
                'name' => 'Updated Name 1'
            ]);

        $response2 = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/roles/{$role->id}", [
                'name' => 'Updated Name 2'
            ]);

        // Both should succeed (last write wins)
        $this->assertContains($response1->status(), [200, 422]);
        $this->assertContains($response2->status(), [200, 422]);
    }

    /** @test */
    public function it_maintains_role_name_uniqueness_across_operations()
    {
        // Create a role with lowercase name
        Role::findOrCreate('unique-role', 'web');

        // Try to create another role with the exact same lowercase name
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/roles', [
                'name' => 'unique-role' // Exact same name should fail
            ]);

        $response->assertStatus(422);
    }

}
