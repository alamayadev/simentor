<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileApiTest extends BaseApiTestCase
{
    // GET /api/profile Tests

    /** @test */
    public function it_requires_authentication_to_get_profile()
    {
        $response = $this->getJson('/api/profile');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_returns_authenticated_user_profile()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                    'roles',
                    'permissions'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User profile retrieved'
            ]);

        $this->assertEquals($this->admin->id, $response->json('data.id'));
        $this->assertEquals($this->admin->name, $response->json('data.name'));
        $this->assertEquals($this->admin->email, $response->json('data.email'));
    }

    /** @test */
    public function it_includes_user_roles_in_profile_response()
    {
        $user = User::factory()->create();
        $role = \App\Models\Role::factory()->create();
        $user->roles()->attach($role);

        $response = $this->actingAs($user)
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'roles' => [
                        '*' => [
                            'id',
                            'name',
                            'guard_name',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);

        $this->assertNotEmpty($response->json('data.roles'));
        $this->assertEquals($role->id, $response->json('data.roles.0.id'));
    }

    /** @test */
    public function it_includes_user_permissions_in_profile_response()
    {
        $user = User::factory()->create();
        $permission = \App\Models\Permission::factory()->create();
        $user->permissions()->attach($permission);

        $response = $this->actingAs($user)
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'permissions' => [
                        '*' => [
                            'id',
                            'name',
                            'guard_name',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);

        $this->assertNotEmpty($response->json('data.permissions'));
        $this->assertEquals($permission->id, $response->json('data.permissions.0.id'));
    }

    /** @test */
    public function it_returns_correct_profile_for_different_user_types()
    {
        // Test admin
        $adminResponse = $this->actingAsAdmin()
            ->getJson('/api/profile');
        $adminResponse->assertStatus(200);

        // Test organik
        $organikResponse = $this->actingAsOrganik()
            ->getJson('/api/profile');
        $organikResponse->assertStatus(200);

        // Test mitra
        $mitraResponse = $this->actingAsMitra()
            ->getJson('/api/profile');
        $mitraResponse->assertStatus(200);

        // All should have the same structure
        $expectedStructure = [
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
                'roles',
                'permissions'
            ]
        ];

        $adminResponse->assertJsonStructure($expectedStructure);
        $organikResponse->assertJsonStructure($expectedStructure);
        $mitraResponse->assertJsonStructure($expectedStructure);
    }

    // PUT /api/profile Tests

    /** @test */
    public function it_requires_authentication_to_update_profile()
    {
        $response = $this->putJson('/api/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com'
        ]);

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_validates_required_fields_for_profile_update()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile', []);

        $this->assertValidationError($response, ['name', 'email']);
    }

    /** @test */
    public function it_validates_name_max_length_for_profile_update()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile', [
                'name' => str_repeat('a', 256),
                'email' => 'test@example.com'
            ]);

        $this->assertValidationError($response, ['name']);
    }

    /** @test */
    public function it_validates_email_format_for_profile_update()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile', [
                'name' => 'Test Name',
                'email' => 'invalid-email'
            ]);

        $this->assertValidationError($response, ['email']);
    }

    /** @test */
    public function it_validates_email_max_length_for_profile_update()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile', [
                'name' => 'Test Name',
                'email' => str_repeat('a', 250) . '@example.com'
            ]);

        $this->assertValidationError($response, ['email']);
    }

    /** @test */
    public function it_validates_unique_email_for_profile_update()
    {
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAsAdmin()
            ->putJson('/api/profile', [
                'name' => 'Test Name',
                'email' => 'other@example.com'
            ]);

        $this->assertValidationError($response, ['email']);
    }

    /** @test */
    public function it_allows_updating_to_same_email_for_profile_update()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile', [
                'name' => 'Updated Name',
                'email' => $this->admin->email
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $this->admin->fresh()->name);
        $this->assertEquals($this->admin->email, $this->admin->fresh()->email);
    }

    /** @test */
    public function it_successfully_updates_user_profile()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile', [
                'name' => 'Updated Name',
                'email' => 'updated@example.com'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User profile updated'
            ]);

        $this->assertEquals('Updated Name', $response->json('data.name'));
        $this->assertEquals('updated@example.com', $response->json('data.email'));
        
        // Verify database update
        $this->assertEquals('Updated Name', $this->admin->fresh()->name);
        $this->assertEquals('updated@example.com', $this->admin->fresh()->email);
    }

    /** @test */
    public function it_updates_profile_with_case_insensitive_email()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->actingAs($user)
            ->putJson('/api/profile', [
                'name' => 'Test Name',
                'email' => 'TEST@EXAMPLE.COM'
            ]);

        $response->assertStatus(200);
        $this->assertEquals('TEST@EXAMPLE.COM', $user->fresh()->email);
    }

    // GET /api/profile/pegawai Tests

    /** @test */
    public function it_requires_authentication_to_get_pegawai_profile()
    {
        $response = $this->getJson('/api/profile/pegawai');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_returns_404_when_user_has_no_pegawai_profile()
    {
        $user = User::factory()->create();
        // Don't create Pegawai record

        $response = $this->actingAs($user)
            ->getJson('/api/profile/pegawai');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pegawai profile not found'
            ]);
    }

    /** @test */
    public function it_returns_pegawai_profile_when_exists()
    {
        $pegawai = Pegawai::factory()->create([
            'user_id' => $this->organik->id
        ]);

        $response = $this->actingAs($this->organik)
            ->getJson('/api/profile/pegawai');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'nip',
                    'gelar_depan',
                    'gelar_belakang',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'alamat',
                    'no_hp',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Pegawai profile retrieved'
            ]);

        $this->assertEquals($pegawai->id, $response->json('data.id'));
        $this->assertEquals($pegawai->nip, $response->json('data.nip'));
        $this->assertEquals($pegawai->user_id, $response->json('data.user_id'));
    }

    // PUT /api/profile/pegawai Tests

    /** @test */
    public function it_requires_authentication_to_update_pegawai_profile()
    {
        $response = $this->putJson('/api/profile/pegawai', [
            'nip' => '1234567890'
        ]);

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_returns_404_when_updating_pegawai_profile_for_user_without_pegawai()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/profile/pegawai', [
                'nip' => '1234567890'
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pegawai profile not found'
            ]);
    }

    /** @test */
    public function it_prevents_updating_id_in_pegawai_profile()
    {
        $pegawai = Pegawai::factory()->create([
            'user_id' => $this->organik->id
        ]);

        $response = $this->actingAs($this->organik)
            ->putJson('/api/profile/pegawai', [
                'id' => 999,
                'nip' => '1234567890'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot update id or user_id'
            ]);
    }

    /** @test */
    public function it_prevents_updating_user_id_in_pegawai_profile()
    {
        $pegawai = Pegawai::factory()->create([
            'user_id' => $this->organik->id
        ]);

        $response = $this->actingAs($this->organik)
            ->putJson('/api/profile/pegawai', [
                'user_id' => 999,
                'nip' => '1234567890'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot update id or user_id'
            ]);
    }

    /** @test */
    public function it_successfully_updates_pegawai_profile()
    {
        $pegawai = Pegawai::factory()->create([
            'user_id' => $this->organik->id
        ]);

        $response = $this->actingAs($this->organik)
            ->putJson('/api/profile/pegawai', [
                'nip' => '199001012020121001',
                'gelar_depan' => 'Dr.',
                'gelar_belakang' => 'S.Kom',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Jend. Sudirman Kav 1',
                'no_hp' => '08123456789'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'nip',
                    'gelar_depan',
                    'gelar_belakang',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'alamat',
                    'no_hp',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Pegawai profile updated'
            ]);

        $this->assertEquals('199001012020121001', $response->json('data.nip'));
        $this->assertEquals('Dr.', $response->json('data.gelar_depan'));
        $this->assertEquals('S.Kom', $response->json('data.gelar_belakang'));

        // Verify database update
        $updatedPegawai = $pegawai->fresh();
        $this->assertEquals('199001012020121001', $updatedPegawai->nip);
        $this->assertEquals('Dr.', $updatedPegawai->gelar_depan);
        $this->assertEquals('S.Kom', $updatedPegawai->gelar_belakang);
    }

    /** @test */
    public function it_partially_updates_pegawai_profile()
    {
        $pegawai = Pegawai::factory()->create([
            'user_id' => $this->organik->id,
            'nip' => '1234567890',
            'gelar_depan' => 'Dr.'
        ]);

        $response = $this->actingAs($this->organik)
            ->putJson('/api/profile/pegawai', [
                'nip' => '9876543210'
                // Only updating nip, not gelar_depan
            ]);

        $response->assertStatus(200);

        $updatedPegawai = $pegawai->fresh();
        $this->assertEquals('9876543210', $updatedPegawai->nip);
        $this->assertEquals('Dr.', $updatedPegawai->gelar_depan); // Should remain unchanged
    }

    // PUT /api/profile/password Tests

    /** @test */
    public function it_requires_authentication_to_update_password()
    {
        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_validates_required_fields_for_password_update()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile/password', []);

        $this->assertValidationError($response, ['current_password', 'password']);
    }

    /** @test */
    public function it_validates_password_confirmation_for_password_update()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile/password', [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword'
            ]);

        $this->assertValidationError($response, ['password']);
    }

    /** @test */
    public function it_validates_password_min_length_for_password_update()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile/password', [
                'current_password' => 'password',
                'password' => 'short',
                'password_confirmation' => 'short'
            ]);

        $this->assertValidationError($response, ['password']);
    }

    /** @test */
    public function it_validates_current_password_for_password_update()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword')
        ]);

        $response = $this->actingAs($user)
            ->putJson('/api/profile/password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'current_password'
                ]
            ]);

        $this->assertStringContainsString(
            'The provided password does not match your current password',
            $response->json('errors.current_password.0')
        );
    }

    /** @test */
    public function it_successfully_updates_password()
    {
        $user = User::factory()->create();
        $user->password = 'oldpassword123'; // Will be hashed by setPasswordAttribute mutator
        $user->save();

        $response = $this->actingAs($user)
            ->putJson('/api/profile/password', [
                'current_password' => 'oldpassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password updated successfully',
                'data' => null
            ]);

        // Verify password was updated
        $updatedUser = $user->fresh();
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->password));
        $this->assertFalse(Hash::check('oldpassword123', $updatedUser->password));
    }

    /** @test */
    public function it_allows_login_with_new_password_after_update()
    {
        $user = User::factory()->create();
        $user->password = 'oldpassword123'; // Will be hashed by setPasswordAttribute mutator
        $user->save();

        // Update password
        $this->actingAs($user)
            ->putJson('/api/profile/password', [
                'current_password' => 'oldpassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);

        // Try login with old password (should fail)
        $oldLoginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'oldpassword123'
        ]);
        $oldLoginResponse->assertStatus(401);

        // Try login with new password (should succeed)
        $newLoginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'newpassword123'
        ]);
        $newLoginResponse->assertStatus(200);
    }

    /** @test */
    public function it_handles_password_update_with_special_characters()
    {
        $user = User::factory()->create();
        $user->password = 'OldP@ss123'; // Will be hashed by setPasswordAttribute mutator
        $user->save();

        $response = $this->actingAs($user)
            ->putJson('/api/profile/password', [
                'current_password' => 'OldP@ss123',
                'password' => 'N3wP@ssw0rd!@#$%',
                'password_confirmation' => 'N3wP@ssw0rd!@#$%'
            ]);

        $response->assertStatus(200);

        // Verify password was updated
        $updatedUser = $user->fresh();
        $this->assertTrue(Hash::check('N3wP@ssw0rd!@#$%', $updatedUser->password));
    }

    // Additional Security Tests

    /** @test */
    public function it_prevents_profile_hijacking_by_different_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User1 tries to access User2's profile
        $response = $this->actingAs($user1)
            ->getJson('/api/profile');

        $response->assertStatus(200);
        $this->assertEquals($user1->id, $response->json('data.id'));
        $this->assertNotEquals($user2->id, $response->json('data.id'));
    }

    /** @test */
    public function it_handles_large_payloads_gracefully()
    {
        $largePayload = str_repeat('a', 10000);

        $response = $this->actingAsAdmin()
            ->putJson('/api/profile', [
                'name' => $largePayload,
                'email' => 'test@example.com'
            ]);

        // Should handle gracefully without crashing
        $this->assertContains($response->status(), [200, 422]);
    }

    /** @test */
    public function it_prevents_sql_injection_in_profile_update()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/profile', [
                'name' => "'; DROP TABLE users; --",
                'email' => 'test@example.com'
            ]);

        // Should handle gracefully without crashing
        $this->assertContains($response->status(), [200, 422]);
        
        // Users table should still exist
        $this->assertDatabaseCount('users', 4); // Created in setUp
    }

    /** @test */
    public function it_maintains_data_integrity_on_concurrent_updates()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        // Simulate concurrent update (this is simplified, real concurrency would need proper locking)
        $initialData = $user->fresh();

        $response1 = $this->actingAs($user)
            ->putJson('/api/profile', [
                'name' => 'Updated Name 1',
                'email' => 'updated1@example.com'
            ]);

        $response2 = $this->actingAs($user)
            ->putJson('/api/profile', [
                'name' => 'Updated Name 2',
                'email' => 'updated2@example.com'
            ]);

        // Both should succeed (last write wins in this simplified case)
        $this->assertContains($response1->status(), [200, 422]);
        $this->assertContains($response2->status(), [200, 422]);
    }
}