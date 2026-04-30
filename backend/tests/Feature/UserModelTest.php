<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_fillable_and_hashed_by_mutator()
    {
        $fillable = (new User())->getFillable();
        $this->assertContains('password', $fillable);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'fillable@example.com',
            'password' => 'plaintext_password',
        ]);

        $this->assertNotSame('plaintext_password', $user->getAuthPassword());
    }

    public function test_password_is_hidden_from_serialization()
    {
        // SECURITY FIX: Verify password is hidden from JSON serialization
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plaintext_password',
        ]);

        $hidden = $user->getHidden();
        $this->assertContains('password', $hidden);
    }

    public function test_user_can_be_created_with_password()
    {
        // SECURITY FIX: Verify User can be created with password (via mutator)
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plaintext_password',
        ]);

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}
