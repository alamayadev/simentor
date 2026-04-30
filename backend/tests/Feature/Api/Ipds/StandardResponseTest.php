<?php

namespace Tests\Feature\Api\Ipds;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AssetIT;
use Spatie\Permission\Models\Role;

class StandardResponseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles before assigning
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('organik', 'web');
        Role::findOrCreate('mitra', 'web');

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function asset_index_returns_success_response()
    {
        AssetIT::create([
            'kode_asset' => 'AST-001',
            'type' => 'hardware',
            'category' => 'Laptop',
            'status' => 'active'
        ]);

        $response = $this->getJson('/api/ipds/assets');

        $response->assertStatus(200);
        // API uses standard wrapper format
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function asset_store_returns_created_response()
    {
        $payload = [
            'kode_asset' => 'AST-002',
            'type' => 'hardware',
            'category' => 'Monitor',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/ipds/assets', $payload);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function asset_not_found_returns_404_response()
    {
        $response = $this->getJson('/api/ipds/assets/99999');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Asset not found'
        ]);
    }
}
