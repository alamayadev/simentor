<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class UserOrganikMitraEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $organikUser;
    protected $mitraUser;
    protected $roles;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->roles = [
            Role::create(['name' => 'super-admin']),
            Role::create(['name' => 'mitra']),
        ];

        // Create super admin user
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->superAdmin->assignRole('super-admin');

        // Debug: Check the actual ID assigned to super admin
        Log::info('Super Admin ID: ' . $this->superAdmin->id);

        // Create organik user (id < 100)
        $this->organikUser = User::create([
            'name' => 'Organik User',
            'email' => 'organik@example.com',
            'password' => bcrypt('password'),
        ]);

        // Debug: Check the actual ID assigned to organik user
        Log::info('Organik User ID: ' . $this->organikUser->id);

        // Create mitra user (id >= 100)
        // We need to create enough users to get an ID >= 100
        for ($i = 0; $i < 100; $i++) {
            User::create([
                'name' => 'Dummy User ' . $i,
                'email' => 'dummy' . $i . '@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        $this->mitraUser = User::create([
            'name' => 'Mitra User',
            'email' => 'mitra@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->mitraUser->assignRole('mitra');

        // Debug: Check the actual ID assigned to mitra user
        Log::info('Mitra User ID: ' . $this->mitraUser->id);
    }

    public function test_organik_endpoint_returns_only_organik_users()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/admin/users/organik');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta',
                'links',
                'roles'
            ])
            // Super Admin (ID 1) should be excluded
            ->assertJsonMissing([
                'name' => 'Super Admin'
            ])
            ->assertJsonFragment([
                'name' => 'Organik User'
            ])
            ->assertJsonMissing([
                'name' => 'Mitra User'
            ]);
    }

    public function test_mitra_endpoint_returns_only_mitra_users()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/admin/users/mitra');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta',
                'links',
                'roles'
            ])
            ->assertJsonFragment([
                'name' => 'Mitra User'
            ])
            ->assertJsonMissing([
                'name' => 'Super Admin'
            ])
            ->assertJsonMissing([
                'name' => 'Organik User'
            ]);
    }

    public function test_organik_endpoint_with_pagination()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/admin/users/organik?page=1&per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta',
                'links',
                'roles'
            ]);
    }

    public function test_mitra_endpoint_with_pagination()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/admin/users/mitra?page=1&per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta',
                'links',
                'roles'
            ]);
    }

    public function test_organik_endpoint_with_filtering()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/admin/users/organik?filter[name]=Organik');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Organik User'
            ])
            ->assertJsonMissing([
                'name' => 'Mitra User'
            ]);
    }

    public function test_mitra_endpoint_with_filtering()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/admin/users/mitra?filter[name]=Mitra');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Mitra User'
            ]);
    }
}
