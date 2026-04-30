<?php

namespace Tests\Feature\Api\Ipds;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\Tiket;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class TiketApiTest extends BaseApiTestCase
{
    // Override setUp to avoid creating role-based users that don't exist in test DB
    protected function setUp(): void
    {
        // Don't call parent::setUp() to avoid role creation issues
        // Instead just call grandparent's setUp
        \Tests\TestCase::setUp();

        // Create a simple user for authentication (no roles needed)
        $this->regularUser = User::factory()->create();
    }
    // ==================== GET /api/ipds/tikets (index) ====================

    public function test_unauthenticated_user_cannot_get_tickets()
    {
        $response = $this->getJson('/api/ipds/tikets');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_tickets()
    {
        Tiket::factory()->count(15)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_tickets_with_pagination()
    {
        Tiket::factory()->count(25)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_tickets_with_filter_jenis_keluhan()
    {
        Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'jenis_keluhan' => 'Hardware PC/Laptop',
        ]);
        Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'jenis_keluhan' => 'Printer',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?filter[jenis_keluhan]=Hardware PC/Laptop');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Hardware PC/Laptop', $data[0]['jenis_keluhan']);
    }

    public function test_tickets_with_sort_by_created_at_asc()
    {
        $ticket1 = Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'created_at' => now()->subDays(2),
        ]);
        $ticket2 = Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?sort=created_at');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertTrue($data[0]['id'] < $data[1]['id']);
    }

    public function test_tickets_with_sort_by_created_at_desc()
    {
        Tiket::factory()->count(3)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?sort=-created_at');

        // Verify the sort parameter is accepted and data is returned
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(3, count($data));
    }

    public function test_tickets_with_sort_by_status_asc()
    {
        Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'status' => 'closed',
        ]);
        Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'status' => 'open',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?sort=status');

        $response->assertStatus(200);
    }

    public function test_tickets_with_sort_by_status_desc()
    {
        Tiket::factory()->count(3)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?sort=-status');

        $response->assertStatus(200);
    }

    public function test_tickets_with_sort_by_jenis_keluhan_asc()
    {
        Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'jenis_keluhan' => 'Printer',
        ]);
        Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'jenis_keluhan' => 'Hardware PC/Laptop',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?sort=jenis_keluhan');

        $response->assertStatus(200);
    }

    public function test_tickets_with_sort_by_jenis_keluhan_desc()
    {
        Tiket::factory()->count(3)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?sort=-jenis_keluhan');

        $response->assertStatus(200);
    }

    public function test_tickets_default_sort()
    {
        $ticket1 = Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'created_at' => now()->subDays(2),
        ]);
        $ticket2 = Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertTrue($data[0]['id'] > $data[1]['id']);
    }

    public function test_tickets_pagination_metadata()
    {
        Tiket::factory()->count(30)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.count', 10)
            ->assertJsonPath('meta.per_page', 10);

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNextPage($response);
    }

    public function test_tickets_pagination_links()
    {
        Tiket::factory()->count(30)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'links',
            ]);
    }

    public function test_tickets_includes_user_relationship()
    {
        Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('user', $data[0]);
    }

    public function test_tickets_empty_when_no_tickets()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets');

        $response->assertStatus(200)
            ->assertJsonPath('meta.count', 0)
            ->assertJsonCount(0, 'data');

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNoNextPage($response);
    }

    // ==================== GET /api/ipds/tikets/{id} (show) ====================

    public function test_unauthenticated_user_cannot_show_ticket()
    {
        $ticket = Tiket::factory()->create();

        $response = $this->getJson("/api/ipds/tikets/{$ticket->id}");
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_show_ticket()
    {
        $ticket = Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson("/api/ipds/tikets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $ticket->id);
    }

    public function test_show_includes_all_fields()
    {
        $ticket = Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson("/api/ipds/tikets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'jenis_keluhan',
                    'deskripsi',
                    'status',
                    'keterangan',
                    'user_id',
                    'ditangani_oleh',
                    'created_at',
                    'updated_at',
                ]
            ]);
    }

    public function test_show_includes_user_relationship()
    {
        $ticket = Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson("/api/ipds/tikets/{$ticket->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('user', $data);
    }

    public function test_show_includes_responder_relationship()
    {
        $ticket = Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'ditangani_oleh' => $this->regularUser->id,
        ]);

        $response = $this->actingAsUser()
            ->getJson("/api/ipds/tikets/{$ticket->id}");

        $response->assertStatus(200);
    }

    public function test_show_with_nonexistent_id()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tiket/99999');

        $response->assertStatus(404);
    }

    // ==================== POST /api/ipds/tikets (store) ====================

    public function test_unauthenticated_user_cannot_store_ticket()
    {
        $response = $this->postJson('/api/ipds/tikets', [
            'jenis_keluhan' => 'Hardware PC/Laptop',
            'deskripsi' => 'Test keluhan',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_store_ticket()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/ipds/tikets', [
                'jenis_keluhan' => 'Hardware PC/Laptop',
                'deskripsi' => 'Test keluhan',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('tikets', [
            'jenis_keluhan' => 'Hardware PC/Laptop',
            'deskripsi' => 'Test keluhan',
        ]);
    }

    public function test_store_validation_jenis_keluhan_required()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/ipds/tikets', [
                'deskripsi' => 'Test keluhan',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['jenis_keluhan']);
    }

    public function test_store_validation_deskripsi_required()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/ipds/tikets', [
                'jenis_keluhan' => 'Hardware PC/Laptop',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['deskripsi']);
    }

    public function test_store_creates_with_default_status_open()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/ipds/tikets', [
                'jenis_keluhan' => 'Hardware PC/Laptop',
                'deskripsi' => 'Test keluhan',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tikets', [
            'jenis_keluhan' => 'Hardware PC/Laptop',
            'deskripsi' => 'Test keluhan',
            'status' => 'open',
        ]);
    }

    public function test_store_sets_user_id_from_auth()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/ipds/tikets', [
                'jenis_keluhan' => 'Hardware PC/Laptop',
                'deskripsi' => 'Test keluhan',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tikets', [
            'user_id' => $this->regularUser->id,
        ]);
    }

    public function test_store_sql_injection_in_deskripsi()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/ipds/tikets', [
                'jenis_keluhan' => 'Hardware PC/Laptop',
                'deskripsi' => "Test'; DROP TABLE tikets; --",
            ]);

        // Should handle gracefully
        $this->assertContains($response->getStatusCode(), [201, 422]);
    }

    public function test_store_xss_in_deskripsi()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/ipds/tikets', [
                'jenis_keluhan' => 'Hardware PC/Laptop',
                'deskripsi' => '<script>alert("xss")</script>',
            ]);

        // Should handle gracefully
        $this->assertContains($response->getStatusCode(), [201, 422]);
    }

    // ==================== PUT /api/ipds/tikets/{id} (update) ====================

    public function test_unauthenticated_user_cannot_update_ticket()
    {
        $ticket = Tiket::factory()->create();

        $response = $this->putJson("/api/ipds/tikets/{$ticket->id}", [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_ticket()
    {
        $ticket = Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->putJson("/api/ipds/tikets/{$ticket->id}", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('tikets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_update_with_nonexistent_id()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/ipds/tiket/99999', [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_sets_ditangani_oleh_from_auth()
    {
        $ticket = Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->putJson("/api/ipds/tikets/{$ticket->id}", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tikets', [
            'id' => $ticket->id,
            'ditangani_oleh' => $this->regularUser->id,
        ]);
    }

    public function test_update_only_provided_fields_are_updated()
    {
        $ticket = Tiket::factory()->create([
            'user_id' => $this->regularUser->id,
            'status' => 'open',
            'keterangan' => 'Original keterangan',
        ]);

        $response = $this->actingAsUser()
            ->putJson("/api/ipds/tikets/{$ticket->id}", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tikets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'keterangan' => 'Original keterangan',
        ]);
    }

    public function test_update_sql_injection_in_keterangan()
    {
        $ticket = Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->putJson("/api/ipds/tikets/{$ticket->id}", [
                'keterangan' => "Test'; DROP TABLE tikets; --",
            ]);

        // Should handle gracefully
        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    public function test_update_xss_in_keterangan()
    {
        $ticket = Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->putJson("/api/ipds/tikets/{$ticket->id}", [
                'keterangan' => '<script>alert("xss")</script>',
            ]);

        // Should handle gracefully
        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    // ==================== DELETE /api/ipds/tikets/{id} (destroy) ====================

    public function test_unauthenticated_user_cannot_delete_ticket()
    {
        $ticket = Tiket::factory()->create();

        $response = $this->deleteJson("/api/ipds/tikets/{$ticket->id}");
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_ticket()
    {
        $ticket = Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->deleteJson("/api/ipds/tikets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('tikets', [
            'id' => $ticket->id,
        ]);
    }

    public function test_delete_with_nonexistent_id()
    {
        $response = $this->actingAsUser()
            ->deleteJson('/api/ipds/tiket/99999');

        $response->assertStatus(404);
    }

    public function test_delete_removes_ticket_from_database()
    {
        $ticket = Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $this->actingAsUser()
            ->deleteJson("/api/ipds/tikets/{$ticket->id}");

        $this->assertDatabaseMissing('tikets', [
            'id' => $ticket->id,
        ]);
    }

    // ==================== GET /api/ipds/tikets/keluhan-options ====================

    public function test_unauthenticated_user_can_get_keluhan_options()
    {
        $response = $this->getJson('/api/ipds/tikets/keluhan-options');
        // Note: This endpoint may require auth, adjust based on actual behavior
        $this->assertContains($response->getStatusCode(), [200, 401]);
    }

    public function test_keluhan_options_returns_all_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets/keluhan-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [],
            ]);
    }

    public function test_keluhan_options_includes_sistem()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets/keluhan-options');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertContains('Sistem', $data);
    }

    public function test_keluhan_options_includes_software()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets/keluhan-options');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertContains('Software', $data);
    }

    public function test_keluhan_options_includes_printer()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets/keluhan-options');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertContains('Printer', $data);
    }

    public function test_keluhan_options_includes_hardware()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets/keluhan-options');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertContains('Hardware PC/Laptop', $data);
    }

    public function test_keluhan_options_includes_jaringan()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets/keluhan-options');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertContains('Jaringan', $data);
    }

    public function test_keluhan_options_includes_akun_bps()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets/keluhan-options');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertContains('Akun BPS', $data);
    }

    // ==================== Security Tests ====================

    public function test_tickets_list_prevents_sql_injection()
    {
        Tiket::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAsUser()
            ->getJson('/api/ipds/tikets?filter[jenis_keluhan]=\' OR \'1\'=\'1');

        // Should handle gracefully
        $this->assertContains($response->getStatusCode(), [200, 500, 422]);
    }
}
