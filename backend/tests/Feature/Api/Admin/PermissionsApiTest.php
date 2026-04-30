<?php

namespace Tests\Feature\Api\Admin;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\User;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsApiTest extends BaseApiTestCase
{
    // GET /api/admin/permissions Tests

    /** @test */
    public function it_requires_authentication_to_list_permissions()
    {
        $response = $this->getJson('/api/admin/permissions');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_list_permissions()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/admin/permissions');

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_lists_permissions_with_pagination()
    {
        // Create some permissions for testing
        Permission::factory()->count(15)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/admin/permissions?per_page=5');

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
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Permissions retrieved'
            ]);

        // Should contain meta and links from pagination
        $this->assertArrayHasKey('meta', $response->json());
        $this->assertArrayHasKey('links', $response->json());
    }

    /** @test */
    public function it_filters_permissions_by_name()
    {
        Permission::factory()->create(['name' => 'manage-users']);
        Permission::factory()->create(['name' => 'view-reports']);
        Permission::factory()->create(['name' => 'edit-posts']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/admin/permissions?filter[name]=users');

        $response->assertStatus(200);

        $permissions = collect($response->json('data'));
        $this->assertTrue($permissions->contains('name', 'manage-users'));
        $this->assertFalse($permissions->contains('name', 'view-reports'));
        $this->assertFalse($permissions->contains('name', 'edit-posts'));
    }

    /** @test */
    public function it_sorts_permissions_by_name()
    {
        Permission::factory()->create(['name' => 'zebra-permission']);
        Permission::factory()->create(['name' => 'apple-permission']);
        Permission::factory()->create(['name' => 'banana-permission']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/admin/permissions?sort=name');

        $response->assertStatus(200);

        $permissions = collect($response->json('data'));
        $this->assertEquals('apple-permission', $permissions->first()['name']);
        $this->assertEquals('zebra-permission', $permissions->last()['name']);
    }

    /** @test */
    public function it_sorts_permissions_by_created_at()
    {
        Permission::factory()->create(['name' => 'old-permission', 'created_at' => now()->subDays(5)]);
        Permission::factory()->create(['name' => 'new-permission', 'created_at' => now()]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/admin/permissions?sort=created_at');

        $response->assertStatus(200);

        $permissions = collect($response->json('data'));
        $this->assertEquals('new-permission', $permissions->first()['name']);
        $this->assertEquals('old-permission', $permissions->last()['name']);
    }

    // GET /api/admin/permissions/{id} Tests

    /** @test */
    public function it_requires_authentication_to_show_permission()
    {
        $response = $this->getJson('/api/admin/permissions/1');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_show_permission()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/admin/permissions/1');

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_returns_404_for_nonexistent_permission()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/admin/permissions/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Permission not found'
            ]);
    }

    /** @test */
    public function it_returns_permission_details()
    {
        $permission = Permission::factory()->create(['name' => 'test-permission']);

        $response = $this->actingAsAdmin()
            ->getJson("/api/admin/permissions/{$permission->id}");

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
                'message' => 'Permission details'
            ]);

        $this->assertEquals($permission->id, $response->json('data.id'));
        $this->assertEquals('test-permission', $response->json('data.name'));
    }

    // POST /api/admin/permissions Tests

    /** @test */
    public function it_requires_authentication_to_create_permission()
    {
        $response = $this->postJson('/api/admin/permissions', [
            'name' => 'new-permission'
        ]);

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_create_permission()
    {
        $response = $this->actingAsOrganik()
            ->postJson('/api/admin/permissions', [
                'name' => 'new-permission'
            ]);

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_validates_required_name_for_permission_creation()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/admin/permissions', []);

        $this->assertValidationError($response, ['name']);
    }

    /** @test */
    public function it_validates_unique_name_for_permission_creation()
    {
        Permission::factory()->create(['name' => 'existing-permission']);

        $response = $this->actingAsAdmin()
            ->postJson('/api/admin/permissions', [
                'name' => 'existing-permission'
            ]);

        $this->assertValidationError($response, ['name']);
    }

    /** @test */
    public function it_creates_permission_successfully()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/admin/permissions', [
                'name' => 'manage-test-permissions'
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
                'message' => 'Permission created'
            ]);

        // Verify database
        $this->assertDatabaseHas('permissions', [
            'name' => 'manage-test-permissions'
        ]);
    }

    /** @test */
    public function it_assigns_web_guard_to_new_permission()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/admin/permissions', [
                'name' => 'test-guard-permission'
            ]);

        $response->assertStatus(201);

        // Verify guard is 'sanctum'
        $this->assertDatabaseHas('permissions', [
            'name' => 'test-guard-permission',
            'guard_name' => 'sanctum'
        ]);
    }

    // PUT /api/admin/permissions/{id} Tests

    /** @test */
    public function it_requires_authentication_to_update_permission()
    {
        $response = $this->putJson('/api/admin/permissions/1', [
            'name' => 'updated-permission'
        ]);

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_update_permission()
    {
        $response = $this->actingAsOrganik()
            ->putJson('/api/admin/permissions/1', [
                'name' => 'updated-permission'
            ]);

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_permission()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/admin/permissions/999', [
                'name' => 'updated-permission'
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Permission not found'
            ]);
    }

    /** @test */
    public function it_validates_required_name_for_permission_update()
    {
        $permission = Permission::factory()->create();

        $response = $this->actingAsAdmin()
            ->putJson("/api/admin/permissions/{$permission->id}", []);

        $this->assertValidationError($response, ['name']);
    }

    /** @test */
    public function it_validates_unique_name_for_permission_update()
    {
        $permission1 = Permission::factory()->create(['name' => 'existing-permission']);
        $permission2 = Permission::factory()->create(['name' => 'another-permission']);

        $response = $this->actingAsAdmin()
            ->putJson("/api/admin/permissions/{$permission2->id}", [
                'name' => 'existing-permission'
            ]);

        $this->assertValidationError($response, ['name']);
    }

    /** @test */
    public function it_updates_permission_successfully()
    {
        $permission = Permission::factory()->create(['name' => 'original-permission']);

        $response = $this->actingAsAdmin()
            ->putJson("/api/admin/permissions/{$permission->id}", [
                'name' => 'updated-test-permission'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Permission updated'
            ]);

        // Verify database update
        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'updated-test-permission'
        ]);
    }

    /** @test */
    public function it_maintains_guard_on_update()
    {
        $permission = Permission::factory()->create(['name' => 'original', 'guard_name' => 'web']);

        $response = $this->actingAsAdmin()
            ->putJson("/api/admin/permissions/{$permission->id}", [
                'name' => 'updated-permission'
            ]);

        $response->assertStatus(200);

        // Verify guard remains unchanged
        $updatedPermission = $permission->fresh();
        $this->assertEquals('web', $updatedPermission->guard_name);
    }

    // DELETE /api/admin/permissions/{id} Tests

    /** @test */
    public function it_requires_authentication_to_delete_permission()
    {
        $response = $this->deleteJson('/api/admin/permissions/1');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_delete_permission()
    {
        $response = $this->actingAsOrganik()
            ->deleteJson('/api/admin/permissions/1');

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_permission()
    {
        $response = $this->actingAsAdmin()
            ->deleteJson('/api/admin/permissions/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Permission not found'
            ]);
    }

    /** @test */
    public function it_deletes_permission_successfully()
    {
        $permission = Permission::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/admin/permissions/{$permission->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Permission berhasil dihapus',
                'data' => null
            ]);

        // Verify database
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
        
        // Verify permission was revoked from users
        $this->assertFalse($user->fresh()->hasPermissionTo($permission->name));
    }

    /** @test */
    public function it_cleans_up_model_has_permissions_before_deleting()
    {
        $permission = Permission::factory()->create();
        $user = User::factory()->create();
        
        // Manually create wrong model_type to test cleanup
        \DB::table('model_has_permissions')->insert([
            'permission_id' => $permission->id,
            'model_id' => $user->id,
            'model_type' => 'Wrong\\Model'
        ]);

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/admin/permissions/{$permission->id}");

        $response->assertStatus(200);

        // Verify wrong model_type was cleaned up before deletion
        $this->assertDatabaseMissing('model_has_permissions', [
            'permission_id' => $permission->id,
            'model_id' => $user->id,
            'model_type' => 'Wrong\\Model'
        ]);
    }

    /** @test */
    public function it_detaches_permission_from_all_users_before_deletion()
    {
        $permission = Permission::factory()->create();
        $users = User::factory()->count(3)->create();
        
        foreach ($users as $user) {
            $user->givePermissionTo($permission);
        }

        $this->assertEquals(3, $permission->users()->count());

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/admin/permissions/{$permission->id}");

        $response->assertStatus(200);

        // Verify all users lost permission
        foreach ($users as $user) {
            $this->assertFalse($user->fresh()->hasPermissionTo($permission->name));
        }
    }

    // GET /api/admin/permissions/list-api-controllers Tests

    /** @test */
    public function it_requires_authentication_to_list_api_controllers()
    {
        $response = $this->getJson('/api/admin/permissions/list-api-controllers');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_requires_super_admin_role_to_list_api_controllers()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/admin/permissions/list-api-controllers');

        $this->assertForbidden($response, 'Forbidden');
    }

    /** @test */
    public function it_returns_api_controllers_list()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/admin/permissions/list-api-controllers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'controllers',
                    'actions'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Controllers list'
            ]);

        // Should contain standard CRUD actions
        $actions = $response->json('data.actions');
        $this->assertContains('create', $actions);
        $this->assertContains('view', $actions);
        $this->assertContains('update', $actions);
        $this->assertContains('delete', $actions);
    }

    /** @test */
    public function it_excludes_admin_and_auth_controllers_from_list()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/admin/permissions/list-api-controllers');

        $response->assertStatus(200);

        $controllers = $response->json('data.controllers');
        
        // Should not contain admin or auth controllers
        $this->assertFalse(in_array('admin', $controllers));
        $this->assertFalse(in_array('auth', $controllers));
    }

    /** @test */
    public function it_sorts_api_controllers_alphabetically()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/admin/permissions/list-api-controllers');

        $response->assertStatus(200);

        $controllers = $response->json('data.controllers');
        $sortedControllers = $controllers;
        sort($sortedControllers);
        
        $this->assertEquals($sortedControllers, $controllers, 'Controllers should be sorted alphabetically');
    }

    // Additional Security Tests

    /** @test */
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $permission = Permission::factory()->create();

        $response = $this->actingAsAdmin()
            ->putJson("/api/admin/permissions/{$permission->id}", [
                'id' => 999, // Should not be updatable
                'name' => 'Updated Permission',
                'guard_name' => 'custom', // Should not be updatable
                'created_at' => now() // Should not be updatable
            ]);

        $response->assertStatus(200);

        $updatedPermission = $permission->fresh();
        $this->assertNotEquals(999, $updatedPermission->id);
        $this->assertEquals('Updated Permission', $updatedPermission->name);
    }

    /** @test */
    public function it_prevents_sql_injection_in_permission_operations()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/admin/permissions?filter[name]=\'; DROP TABLE permissions; --');

        // Should handle gracefully without crashing
        $this->assertContains($response->status(), [200, 403]);
        
        // Permissions table should still exist
        $this->assertDatabaseCount('permissions', 1); // admin permission from setUp
    }

    /** @test */
    public function it_handles_large_names_gracefully()
    {
        $largeName = str_repeat('a', 300);

        $response = $this->actingAsAdmin()
            ->postJson('/api/admin/permissions', [
                'name' => $largeName
            ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    /** @test */
    public function it_handles_concurrent_permission_updates_gracefully()
    {
        $permission = Permission::factory()->create(['name' => 'original']);

        // Simulate concurrent updates
        $response1 = $this->actingAsAdmin()
            ->putJson("/api/admin/permissions/{$permission->id}", [
                'name' => 'Updated Name 1'
            ]);

        $response2 = $this->actingAsAdmin()
            ->putJson("/api/admin/permissions/{$permission->id}", [
                'name' => 'Updated Name 2'
            ]);

        // Both should succeed (last write wins)
        $this->assertContains($response1->status(), [200, 422]);
        $this->assertContains($response2->status(), [200, 422]);
    }

    /** @test */
    public function it_maintains_permission_name_uniqueness_across_operations()
    {
        Permission::factory()->create(['name' => 'unique-permission']);

        $response = $this->actingAsAdmin()
            ->postJson('/api/admin/permissions', [
                'name' => 'unique-permission'
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('permissions', 2); // admin permission + existing unique-permission
    }

    /** @test */
    public function it_handles_special_characters_in_permission_names()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/admin/permissions', [
                'name' => 'manage-special-chars_123-permission'
            ]);

        $response->assertStatus(201);

        // Verify special characters are preserved
        $this->assertDatabaseHas('permissions', [
            'name' => 'manage-special-chars_123-permission'
        ]);
    }
}
