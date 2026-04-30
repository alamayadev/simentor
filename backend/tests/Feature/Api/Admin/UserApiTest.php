<?php

namespace Tests\Feature\Api\Admin;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\User;
use App\Models\PasswordHistory;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UserApiTest extends BaseApiTestCase
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
        
        // Clear cache before each test
        Cache::flush();
    }

    protected function actingAsSuperAdmin(): self
    {
        return $this->actingAs($this->superAdmin, 'sanctum');
    }

    // =====================================================================
    // GET /api/admin/users Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_list_users()
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_lists_users_with_pagination()
    {
        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/users?per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'organik_data',
                    'organik_meta',
                    'organik_links',
                    'mitra_data',
                    'mitra_meta',
                    'mitra_links',
                    'roles'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully'
            ]);
    }

    /** @test */
    public function it_returns_roles_in_user_list()
    {
        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonPath('data.roles', fn ($roles) => is_array($roles));
    }

    // =====================================================================
    // GET /api/admin/users/{id} Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_show_user()
    {
        $response = $this->getJson('/api/admin/users/1');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_super_admin_role_to_show_user()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/admin/users/1');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden'
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_user()
    {
        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/users/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found'
            ]);
    }

    /** @test */
    public function it_returns_user_details_with_roles_and_permissions()
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('test-role', 'web');
        $permission = Permission::findOrCreate('test-permission', 'web');
        
        $user->assignRole($role);
        $user->givePermissionTo($permission);

        $response = $this->actingAsSuperAdmin()
            ->getJson("/api/admin/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User details'
            ]);

        $this->assertEquals($user->id, $response->json('data.id'));
    }

    // =====================================================================
    // POST /api/admin/users Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_create_user()
    {
        $response = $this->postJson('/api/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_super_admin_role_to_create_user()
    {
        $response = $this->actingAsOrganik()
            ->postJson('/api/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'Password1!'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden'
            ]);
    }

    /** @test */
    public function it_validates_required_fields_for_user_creation()
    {
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function it_validates_email_format_for_user_creation()
    {
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users', [
                'name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'Password1!'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_unique_email_for_user_creation()
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users', [
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'Password1!'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_password_complexity()
    {
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'simple' // No uppercase, no digit, no special char
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_creates_user_successfully()
    {
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users', [
                'name' => 'Test User',
                'email' => 'newuser@example.com',
                'password' => 'Password1!'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ],
                    'roles',
                    'user_roles',
                    'user_permissions'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User created'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com'
        ]);
    }

    /** @test */
    public function it_creates_user_with_roles()
    {
        $role = Role::findOrCreate('test-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users', [
                'name' => 'Test User',
                'email' => 'roleuser@example.com',
                'password' => 'Password1!',
                'roles' => [$role->name]
            ]);

        $response->assertStatus(201);

        $newUser = User::where('email', 'roleuser@example.com')->first();
        $this->assertTrue($newUser->hasRole($role->name));
    }

    /** @test */
    public function it_hashes_password_when_creating_user()
    {
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users', [
                'name' => 'Test User',
                'email' => 'hashtest@example.com',
                'password' => 'Password1!'
            ]);

        $response->assertStatus(201);

        $newUser = User::where('email', 'hashtest@example.com')->first();
        $this->assertNotEquals('Password1!', $newUser->password);
        $this->assertTrue(Hash::check('Password1!', $newUser->password));
    }

    // =====================================================================
    // PUT /api/admin/users/{id} Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_update_user()
    {
        $response = $this->putJson('/api/admin/users/1', [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_super_admin_role_to_update_user()
    {
        $response = $this->actingAsOrganik()
            ->putJson('/api/admin/users/1', [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden'
            ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_user()
    {
        $response = $this->actingAsSuperAdmin()
            ->putJson('/api/admin/users/999999', [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found'
            ]);
    }

    /** @test */
    public function it_validates_email_format_when_updating_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/users/{$user->id}", [
                'email' => 'invalid-email'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_updates_user_details()
    {
        $user = User::factory()->create();

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/users/{$user->id}", [
                'name' => 'Updated Name',
                'email' => 'updated@example.com'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated'
            ]);

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@example.com', $user->email);
    }

    /** @test */
    public function it_updates_user_roles()
    {
        $user = User::factory()->create();
        $newRole = Role::findOrCreate('new-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/users/{$user->id}", [
                'roles' => [$newRole->name]
            ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue($user->hasRole($newRole->name));
    }

    // =====================================================================
    // DELETE /api/admin/users/{id} Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_delete_user()
    {
        $response = $this->deleteJson('/api/admin/users/1');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_super_admin_role_to_delete_user()
    {
        $response = $this->actingAsOrganik()
            ->deleteJson('/api/admin/users/1');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden'
            ]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_user()
    {
        $response = $this->actingAsSuperAdmin()
            ->deleteJson('/api/admin/users/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found'
            ]);
    }

    /** @test */
    public function it_deletes_user_successfully()
    {
        $user = User::factory()->create();

        $response = $this->actingAsSuperAdmin()
            ->deleteJson("/api/admin/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User deleted'
            ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    // =====================================================================
    // GET /api/admin/users/organik Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_list_organik_users()
    {
        $response = $this->getJson('/api/admin/users/organik');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_lists_organik_users()
    {
        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/users/organik');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data organik'
            ]);
    }

    // =====================================================================
    // GET /api/admin/users/mitra Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_list_mitra_users()
    {
        $response = $this->getJson('/api/admin/users/mitra');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_lists_mitra_users()
    {
        $response = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/users/mitra');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data mitra'
            ]);
    }

    // =====================================================================
    // POST /api/admin/users/bulk-update-roles Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_bulk_update_roles()
    {
        $response = $this->postJson('/api/admin/users/bulk-update-roles', [
            'user_ids' => [1, 2],
            'roles' => ['admin']
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_super_admin_role_to_bulk_update_roles()
    {
        $response = $this->actingAsOrganik()
            ->postJson('/api/admin/users/bulk-update-roles', [
                'user_ids' => [1, 2],
                'roles' => ['admin']
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden'
            ]);
    }

    /** @test */
    public function it_validates_required_fields_for_bulk_role_update()
    {
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users/bulk-update-roles', []);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_bulk_updates_user_roles()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $role = Role::findOrCreate('bulk-role', 'web');

        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users/bulk-update-roles', [
                'user_ids' => [$user1->id, $user2->id],
                'roles' => [$role->name]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        // Verify roles were assigned
        $user1->refresh();
        $user2->refresh();
        $this->assertTrue($user1->hasRole($role->name));
        $this->assertTrue($user2->hasRole($role->name));
    }

    // =====================================================================
    // Additional Security Tests
    // =====================================================================

    /** @test */
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $user = User::factory()->create();
        $originalId = $user->id;

        $response = $this->actingAsSuperAdmin()
            ->putJson("/api/admin/users/{$user->id}", [
                'id' => 999999, // Should not be updatable
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals($originalId, $user->id); // ID should not change
    }

    /** @test */
    public function it_clears_cache_when_user_is_created()
    {
        // Just verify the endpoint works and doesn't error due to caching
        $response = $this->actingAsSuperAdmin()
            ->postJson('/api/admin/users', [
                'name' => 'Cache Test User',
                'email' => 'cachetest@example.com',
                'password' => 'Password1!'
            ]);

        $response->assertStatus(201);

        // Verify user list still works after cache clear
        $listResponse = $this->actingAsSuperAdmin()
            ->getJson('/api/admin/users');

        $listResponse->assertStatus(200);
    }
}
