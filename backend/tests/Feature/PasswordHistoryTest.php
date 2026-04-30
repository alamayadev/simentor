<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PasswordHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PasswordHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create super-admin role
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

        // Create a super-admin user for authentication
        $this->admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('Admin123@'),
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_password_history_created_on_update()
    {
        // SECURITY FIX: Verify password history is saved when user password is updated
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'passwordhistory@example.com',
            'password' => bcrypt('OldPass123@'),
        ]);

        // Update password
        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'NewPass123@',
            ]);

        $response->assertStatus(200);

        // Verify old password was saved to history
        $this->assertDatabaseHas('password_histories', [
            'user_id' => $user->id,
        ]);

        $historyCount = PasswordHistory::where('user_id', $user->id)->count();
        $this->assertEquals(1, $historyCount);
    }

    public function test_cannot_reuse_last_5_passwords()
    {
        // SECURITY FIX: Verify user cannot reuse their last 5 passwords
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'passwordreuse@example.com',
            'password' => bcrypt('Password1@'),
        ]);

        // Create 5 password history entries
        $passwords = ['Password1@', 'Password2@', 'Password3@', 'Password4@', 'Password5@'];
        foreach ($passwords as $password) {
            PasswordHistory::create([
                'user_id' => $user->id,
                'password_hash' => bcrypt($password),
            ]);
        }

        // Try to reuse Password3@ (should fail)
        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'Password3@',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Password reuse detected',
            ]);
    }

    public function test_can_use_password_older_than_last_5()
    {
        // SECURITY FIX: Verify user can reuse passwords older than last 5
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'oldpassword@example.com',
            'password' => bcrypt('CurrentPass123@'),
        ]);

        // Create 6 password history entries with explicit timestamps to ensure ordering
        $now = now();
        for ($i = 1; $i <= 6; $i++) {
            $history = new PasswordHistory([
                'user_id' => $user->id,
                'password_hash' => bcrypt("Password{$i}@"),
            ]);
            $history->created_at = $now->copy()->subHours(7 - $i); // 6,5,4,3,2,1 hours ago
            $history->updated_at = $history->created_at;
            $history->save();
        }

        // Password1@ is 6 hours old (outside last 5 which are 5,4,3,2,1 hours ago)
        // So Password1@ should be allowed
        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'Password1@',
            ]);

        $response->assertStatus(200);
    }
}
