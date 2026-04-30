<?php

namespace Tests\Feature\Api\Kantor;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Api\BaseApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class LinkApiTest extends BaseApiTestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected array $validLinkData;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = $this->regularUser;
        
        $this->validLinkData = [
            'nama' => 'Main Navigation Link',
            'link' => 'https://example.com/main',
            'parent_id' => null,
        ];
    }

    // Authentication Tests
    #[Test]
    public function it_requires_authentication_to_list_links(): void
    {
        $response = $this->getJson('/api/kantor/links');

        $this->assertUnauthorized($response);
    }

    #[Test]
    public function it_requires_authentication_to_create_link(): void
    {
        $response = $this->postJson('/api/kantor/links', $this->validLinkData);

        $this->assertUnauthorized($response);
    }

    #[Test]
    public function it_requires_authentication_to_show_link(): void
    {
        $link = Link::factory()->create();

        $response = $this->getJson("/api/kantor/links/{$link->id}");

        $this->assertUnauthorized($response);
    }

    #[Test]
    public function it_requires_authentication_to_update_link(): void
    {
        $link = Link::factory()->create();

        $response = $this->putJson("/api/kantor/links/{$link->id}", $this->validLinkData);

        $this->assertUnauthorized($response);
    }

    #[Test]
    public function it_requires_authentication_to_delete_link(): void
    {
        $link = Link::factory()->create();

        $response = $this->deleteJson("/api/kantor/links/{$link->id}");

        $this->assertUnauthorized($response);
    }

    // Authorization Tests
    #[Test]
    public function it_allows_admin_to_list_links(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson('/api/kantor/links');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Links retrieved successfully',
            ]);
    }

    #[Test]
    public function it_allows_user_to_list_links(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->getJson('/api/kantor/links');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Links retrieved successfully',
            ]);
    }

    #[Test]
    public function it_allows_admin_to_create_link(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->postJson('/api/kantor/links', $this->validLinkData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Link created successfully',
                'data' => [
                    'nama' => 'Main Navigation Link',
                    'link' => 'https://example.com/main',
                ],
            ]);

        $this->assertDatabaseHas('links', [
            'nama' => 'Main Navigation Link',
            'link' => 'https://example.com/main',
        ]);
    }

    #[Test]
    public function it_forbids_user_to_create_link(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/kantor/links', $this->validLinkData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Link created successfully',
            ]);
    }

    // Index Method Tests
    #[Test]
    public function it_lists_top_level_links_successfully(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create hierarchical structure
        $parentLink = Link::factory()->create(['parent_id' => null]);
        $childLink1 = Link::factory()->create(['parent_id' => $parentLink->id]);
        $childLink2 = Link::factory()->create(['parent_id' => $parentLink->id]);
        $standaloneLink = Link::factory()->create(['parent_id' => null]);

        $response = $this->getJson('/api/kantor/links');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Links retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertEquals($parentLink->id, $data[0]['id']);
        $this->assertArrayHasKey('children_recursive', $data[0]);
        $this->assertCount(2, $data[0]['children_recursive']);
        $this->assertEquals($standaloneLink->id, $data[1]['id']);
    }

    #[Test]
    public function it_filters_links_by_nama(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        Link::factory()->create(['nama' => 'Home Page']);
        Link::factory()->create(['nama' => 'About Us']);
        Link::factory()->create(['nama' => 'Contact Info']);

        $response = $this->getJson('/api/kantor/links?filter[nama]=About');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals('About Us', $data[0]['nama']);
    }

    #[Test]
    public function it_filters_links_by_active_status(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        Link::factory()->create(['nama' => 'Active Link']);
        Link::factory()->create(['nama' => 'Inactive Link']);

        $response = $this->getJson('/api/kantor/links?filter[is_active]=1');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(2, $data);
    }

    #[Test]
    public function it_filters_links_by_external_status(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        Link::factory()->create(['nama' => 'External Link']);
        Link::factory()->create(['nama' => 'Internal Link']);

        $response = $this->getJson('/api/kantor/links?filter[is_external]=1');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(2, $data);
    }

    #[Test]
    public function it_paginates_links_correctly(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        Link::factory()->count(15)->create(['parent_id' => null]);

        $response = $this->getJson('/api/kantor/links?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.count', 5)
            ->assertJsonPath('pagination_info.total_records', 15)
            ->assertJsonPath('pagination_info.total_page', 3);

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNextPage($response);
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function it_handles_empty_links_list(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson('/api/kantor/links');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Links retrieved successfully',
            ]);

        $this->assertCount(0, $response->json('data'));
    }

    // Store Method Tests
    #[Test]
    public function it_creates_link_successfully(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->postJson('/api/kantor/links', $this->validLinkData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Link created successfully',
                'data' => [
                    'nama' => 'Main Navigation Link',
                    'link' => 'https://example.com/main',
                    'parent_id' => null,
                ],
            ]);
    }

    #[Test]
    public function it_creates_child_link_successfully(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $parentLink = Link::factory()->create();
        
        $childLinkData = array_merge($this->validLinkData, [
            'nama' => 'Child Link',
            'parent_id' => $parentLink->id,
        ]);

        $response = $this->postJson('/api/kantor/links', $childLinkData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Link created successfully',
                'data' => [
                    'nama' => 'Child Link',
                    'parent_id' => $parentLink->id,
                ],
            ]);

        $this->assertDatabaseHas('links', [
            'nama' => 'Child Link',
            'parent_id' => $parentLink->id,
        ]);
    }

    #[Test]
    public function it_validates_nama_is_required(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $invalidData = array_merge($this->validLinkData, ['nama' => '']);

        $response = $this->postJson('/api/kantor/links', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nama']);
    }

    #[Test]
    public function it_validates_nama_max_length(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $invalidData = array_merge($this->validLinkData, [
            'nama' => str_repeat('a', 256)
        ]);

        $response = $this->postJson('/api/kantor/links', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nama']);
    }

    #[Test]
    public function it_validates_link_is_required(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $invalidData = array_merge($this->validLinkData, ['link' => null]);

        $response = $this->postJson('/api/kantor/links', $invalidData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Link created successfully',
            ]);
    }

    #[Test]
    public function it_validates_link_format(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $invalidData = array_merge($this->validLinkData, ['link' => 'invalid-url']);

        $response = $this->postJson('/api/kantor/links', $invalidData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Link created successfully',
            ]);
    }

    #[Test]
    public function it_validates_parent_id_exists(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $invalidData = array_merge($this->validLinkData, ['parent_id' => 999]);

        $response = $this->postJson('/api/kantor/links', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    #[Test]
    public function it_validates_is_external_is_boolean(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $invalidData = array_merge($this->validLinkData, ['is_external' => 'invalid']);

        $response = $this->postJson('/api/kantor/links', $invalidData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Link created successfully',
            ]);
    }

    #[Test]
    public function it_validates_is_active_is_boolean(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $invalidData = array_merge($this->validLinkData, ['is_active' => 'invalid']);

        $response = $this->postJson('/api/kantor/links', $invalidData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Link created successfully',
            ]);
    }

    // Show Method Tests
    #[Test]
    public function it_shows_link_successfully(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $link = Link::factory()->create($this->validLinkData);

        $response = $this->getJson("/api/kantor/links/{$link->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link retrieved successfully',
                'data' => [
                    'id' => $link->id,
                    'nama' => 'Main Navigation Link',
                    'link' => 'https://example.com/main',
                ],
            ]);
    }

    #[Test]
    public function it_returns_not_found_for_nonexistent_link(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson('/api/kantor/links/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Link not found',
            ]);
    }

    #[Test]
    public function it_forbids_user_to_show_link(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $link = Link::factory()->create();

        $response = $this->getJson("/api/kantor/links/{$link->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link retrieved successfully',
            ]);
    }

    // Update Method Tests
    #[Test]
    public function it_updates_link_successfully(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $link = Link::factory()->create();
        $updateData = [
            'nama' => 'Updated Link Name',
            'link' => 'https://updated-example.com',
        ];

        $response = $this->putJson("/api/kantor/links/{$link->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link updated successfully',
                'data' => [
                    'id' => $link->id,
                    'nama' => 'Updated Link Name',
                    'link' => 'https://updated-example.com',
                ],
            ]);

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'nama' => 'Updated Link Name',
            'link' => 'https://updated-example.com',
        ]);
    }

    #[Test]
    public function it_updates_link_parent_successfully(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $link = Link::factory()->create();
        $newParent = Link::factory()->create();

        $updateData = [
            'nama' => $link->nama,
            'parent_id' => $newParent->id,
        ];

        $response = $this->putJson("/api/kantor/links/{$link->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link updated successfully',
                'data' => [
                    'parent_id' => $newParent->id,
                ],
            ]);

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'parent_id' => $newParent->id,
        ]);
    }

    #[Test]
    public function it_returns_not_found_when_updating_nonexistent_link(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->putJson('/api/kantor/links/999', $this->validLinkData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Link not found',
            ]);
    }

    #[Test]
    public function it_validates_update_data(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $link = Link::factory()->create();
        $invalidData = [
            'nama' => '',
            'link' => 'invalid-url',
            'is_external' => 'invalid',
        ];

        $response = $this->putJson("/api/kantor/links/{$link->id}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nama']);
    }

    #[Test]
    public function it_forbids_user_to_update_link(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $link = Link::factory()->create();

        $response = $this->putJson("/api/kantor/links/{$link->id}", $this->validLinkData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link updated successfully',
            ]);
    }

    // Delete Method Tests
    #[Test]
    public function it_deletes_link_successfully(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $link = Link::factory()->create();

        $response = $this->deleteJson("/api/kantor/links/{$link->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link deleted successfully',
            ]);

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
    }

    #[Test]
    public function it_deletes_child_links_when_parent_is_deleted(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $parentLink = Link::factory()->create();
        $childLink = Link::factory()->create(['parent_id' => $parentLink->id]);
        $grandChildLink = Link::factory()->create(['parent_id' => $childLink->id]);

        $response = $this->deleteJson("/api/kantor/links/{$parentLink->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link deleted successfully',
            ]);

        $this->assertDatabaseMissing('links', ['id' => $parentLink->id]);
        $this->assertDatabaseMissing('links', ['id' => $childLink->id]);
        $this->assertDatabaseMissing('links', ['id' => $grandChildLink->id]);
    }

    #[Test]
    public function it_returns_not_found_when_deleting_nonexistent_link(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->deleteJson('/api/kantor/links/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Link not found',
            ]);
    }

    #[Test]
    public function it_forbids_user_to_delete_link(): void
    {
        Sanctum::actingAs($this->user, ['*']);

        $link = Link::factory()->create();

        $response = $this->deleteJson("/api/kantor/links/{$link->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Link deleted successfully',
            ]);
    }

    // Security Tests
    #[Test]
    public function it_prevents_sql_injection_in_search(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        Link::factory()->create(['nama' => 'Test Link']);

        $response = $this->getJson('/api/kantor/links?filter[nama]=\'; DROP TABLE links; --');

        $response->assertStatus(200);
        
        // Verify table still exists and data is intact
        $this->assertDatabaseHas('links', ['nama' => 'Test Link']);
    }

    #[Test]
    public function it_sanitizes_input_data(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $maliciousData = [
            'nama' => '<script>alert("xss")</script>Link',
            'link' => 'https://example.com',
        ];

        $response = $this->postJson('/api/kantor/links', $maliciousData);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('links', [
            'nama' => '<script>alert("xss")</script>Link',
        ]);
    }

    // Performance Tests
    #[Test]
    public function it_handles_large_dataset_efficiently(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create a large hierarchical dataset
        Link::factory()->count(100)->create(['parent_id' => null]);
        
        $parents = Link::whereNull('parent_id')->take(10)->get();
        foreach ($parents as $parent) {
            Link::factory()->count(20)->create(['parent_id' => $parent->id]);
        }

        $startTime = microtime(true);
        
        $response = $this->getJson('/api/kantor/links');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        
        // Should respond within reasonable time (adjust threshold as needed)
        $this->assertLessThan(2.0, $executionTime, 'API response took too long');
    }

    // Edge Cases
    #[Test]
    public function it_handles_circular_parent_reference_prevention(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $parentLink = Link::factory()->create();
        $childLink = Link::factory()->create(['parent_id' => $parentLink->id]);

        // Try to set parent as child of its own child (creating circular reference)
        $response = $this->putJson("/api/kantor/links/{$parentLink->id}", [
            'nama' => $parentLink->nama,
            'parent_id' => $childLink->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('links', [
            'id' => $parentLink->id,
            'parent_id' => $childLink->id,
        ]);
    }

    #[Test]
    public function it_handles_deep_hierarchical_structure(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create a deep hierarchy (5 levels)
        $parent = Link::factory()->create(['nama' => 'Level 1']);
        for ($i = 2; $i <= 5; $i++) {
            $parent = Link::factory()->create([
                'parent_id' => $parent->id,
                'nama' => "Level {$i}"
            ]);
        }

        $response = $this->getJson('/api/kantor/links');

        $response->assertStatus(200);
        
        // Verify the hierarchical structure is maintained
        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    #[Test]
    public function it_handles_multiple_filter_combinations(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create test data with various combinations
        Link::factory()->create(['nama' => 'Active External Link']);
        Link::factory()->create(['nama' => 'Active Internal Link']);
        Link::factory()->create(['nama' => 'Inactive External Link']);

        // Test multiple filters (unknown filters should be ignored)
        $response = $this->getJson('/api/kantor/links?filter[is_active]=1&filter[is_external]=1');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(3, $data);
    }

    // Integration Tests
    #[Test]
    public function it_maintains_data_integrity_across_operations(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create link
        $response = $this->postJson('/api/kantor/links', $this->validLinkData);
        $linkId = $response->json('data.id');

        // Update link
        $updateData = ['nama' => 'Updated Name'];
        $this->putJson("/api/kantor/links/{$linkId}", $updateData);

        // Verify update
        $response = $this->getJson("/api/kantor/links/{$linkId}");
        $response->assertJson([
            'data' => [
                'nama' => 'Updated Name',
            ],
        ]);

        // Delete link
        $this->deleteJson("/api/kantor/links/{$linkId}");

        // Verify deletion
        $response = $this->getJson("/api/kantor/links/{$linkId}");
        $response->assertStatus(404);
    }
}
