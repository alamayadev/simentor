<?php

namespace Tests\Feature\Kantor;

use Tests\TestCase;
use App\Models\User;
use App\Models\Link;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LinkApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    public function test_can_list_links()
    {
        // Create some links
        $link1 = Link::factory()->create(['nama' => 'Dashboard', 'link' => '/dashboard', 'parent_id' => null]);
        $link2 = Link::factory()->create(['nama' => 'Settings', 'link' => '/settings', 'parent_id' => $link1->id]);
        $link3 = Link::factory()->create(['nama' => 'Profile', 'link' => '/profile', 'parent_id' => null]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/links');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'parent_id',
                        'nama',
                        'link',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data');

        // Verify sorting: null parent_id should come first, then sorted by id
        $responseData = $response->json()['data'];
        $this->assertNull($responseData[0]['parent_id']); // First item should have null parent_id
        $this->assertNull($responseData[1]['parent_id']); // Second item should also have null parent_id
        $this->assertEquals($link1->id, $responseData[0]['id']); // First null parent should be first created
        $this->assertEquals($link3->id, $responseData[1]['id']); // Second null parent should be second created
    }

    public function test_can_filter_root_links()
    {
        // Create some links with hierarchy
        $parent = Link::factory()->create(['nama' => 'Parent', 'parent_id' => null]);
        $child = Link::factory()->create(['nama' => 'Child', 'parent_id' => $parent->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/links?roots=true');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'parent_id',
                        'nama',
                        'link',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $parent->id,
                'nama' => 'Parent'
            ]);
    }

    public function test_can_create_link()
    {
        $linkData = [
            'nama' => 'New Link',
            'link' => '/new-link',
            'parent_id' => null
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/kantor/links', $linkData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nama' => 'New Link',
                'link' => '/new-link'
            ]);

        $this->assertDatabaseHas('links', [
            'nama' => 'New Link',
            'link' => '/new-link'
        ]);
    }

    public function test_can_show_link()
    {
        $link = Link::factory()->create(['nama' => 'Test Link', 'link' => '/test-link']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/kantor/links/{$link->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $link->id,
                'nama' => 'Test Link',
                'link' => '/test-link'
            ]);
    }

    public function test_can_update_link()
    {
        $link = Link::factory()->create(['nama' => 'Original Name', 'link' => '/original']);

        $updatedData = [
            'nama' => 'Updated Name',
            'link' => '/updated',
            'parent_id' => null
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/kantor/links/{$link->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'nama' => 'Updated Name',
                'link' => '/updated'
            ]);

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'nama' => 'Updated Name',
            'link' => '/updated'
        ]);
    }

    public function test_can_delete_link()
    {
        $link = Link::factory()->create(['nama' => 'To Delete', 'link' => '/delete']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/kantor/links/{$link->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('links', [
            'id' => $link->id
        ]);
    }

    public function test_cannot_access_links_without_authentication()
    {
        $response = $this->getJson('/api/kantor/links');

        $response->assertStatus(401);
    }
}
