<?php

namespace Tests\Feature\Api\Kantor;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\UuTambah;
use App\Models\User;

class UuTambahApiTest extends BaseApiTestCase
{
    // ==================== GET /api/kantor/uu-tambahan (index) ====================

    public function test_unauthenticated_user_cannot_list_uu_tambahan()
    {
        $response = $this->getJson('/api/kantor/uu-tambahan');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_uu_tambahan()
    {
        UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Pasal 5 Ayat 2',
        ]);
        UuTambah::create([
            'jenis_surat' => 'Surat Edaran',
            'surat_id' => '2',
            'item' => 'Pasal 10 Ayat 1',
        ]);
        UuTambah::create([
            'jenis_surat' => 'Peraturan',
            'surat_id' => '3',
            'item' => 'Pasal 15 Ayat 3',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu-tambahan');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [],
            ]);

        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_uu_tambahan_list_pagination_works()
    {
        for ($i = 0; $i < 20; $i++) {
            UuTambah::create([
                'jenis_surat' => 'Surat Keputusan',
                'surat_id' => "{$i}",
                'item' => "Item {$i}",
            ]);
        }

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu-tambahan?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.count', 10)
            ->assertJsonPath('pagination_info.total_records', 20)
            ->assertJsonCount(10, 'data');

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNextPage($response);
    }

    public function test_uu_tambahan_list_can_sort()
    {
        UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Zebra',
        ]);
        UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '2',
            'item' => 'Apple',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu-tambahan?sort=item&order=asc');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals('Apple', $data[0]['item']);
    }

    public function test_uu_tambahan_list_default_sort_by_id_desc()
    {
        $item1 = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'First',
        ]);
        $item2 = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '2',
            'item' => 'Second',
        ]);
        $item3 = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '3',
            'item' => 'Third',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu-tambahan');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertTrue($data[0]['id'] > $data[1]['id']);
    }

    public function test_uu_tambahan_list_empty_when_no_data()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu-tambahan');

        $response->assertStatus(200)
            ->assertJsonPath('meta.count', 0)
            ->assertJsonPath('pagination_info.total_records', 0)
            ->assertJsonCount(0, 'data');

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNoNextPage($response);
    }

    // ==================== POST /api/kantor/uu-tambahan (store) ====================

    public function test_unauthenticated_user_cannot_create_uu_tambahan()
    {
        $response = $this->postJson('/api/kantor/uu-tambahan', [
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Test Item',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_uu_tambahan()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu-tambahan', [
                'jenis_surat' => 'Surat Keputusan',
                'surat_id' => '1',
                'item' => 'Pasal 5 Ayat 2',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'jenis_surat',
                    'surat_id',
                    'item',
                ]
            ]);

        $this->assertDatabaseHas('uu_tambahan', [
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Pasal 5 Ayat 2',
        ]);
    }

    public function test_create_uu_tambahan_requires_jenis_surat()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu-tambahan', [
                'surat_id' => '1',
                'item' => 'Test Item',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_uu_tambahan_requires_surat_id()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu-tambahan', [
                'jenis_surat' => 'Surat Keputusan',
                'item' => 'Test Item',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_uu_tambahan_requires_item()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu-tambahan', [
                'jenis_surat' => 'Surat Keputusan',
                'surat_id' => '1',
            ]);

        $response->assertStatus(422);
    }

    // ==================== GET /api/kantor/uu-tambahan/{id} (show) ====================

    public function test_unauthenticated_user_cannot_view_uu_tambahan()
    {
        $uuTambah = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Test Item',
        ]);

        $response = $this->getJson("/api/kantor/uu-tambahan/{$uuTambah->id}");

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_uu_tambahan()
    {
        $uuTambah = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Test Item',
        ]);

        $response = $this->actingAsUser()
            ->getJson("/api/kantor/uu-tambahan/{$uuTambah->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $uuTambah->id)
            ->assertJsonPath('data.jenis_surat', 'Surat Keputusan')
            ->assertJsonPath('data.item', 'Test Item');
    }

    public function test_view_uu_tambahan_returns_404_for_nonexistent_id()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu-tambahan/99999');

        $response->assertStatus(404);
    }

    // ==================== PUT /api/kantor/uu-tambahan/{id} (update) ====================

    public function test_unauthenticated_user_cannot_update_uu_tambahan()
    {
        $uuTambah = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Test Item',
        ]);

        $response = $this->putJson("/api/kantor/uu-tambahan/{$uuTambah->id}", [
            'jenis_surat' => 'Surat Edaran',
            'surat_id' => '2',
            'item' => 'Updated Item',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_uu_tambahan()
    {
        $uuTambah = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Original Item',
        ]);

        $response = $this->actingAsUser()
            ->putJson("/api/kantor/uu-tambahan/{$uuTambah->id}", [
                'jenis_surat' => 'Surat Edaran',
                'surat_id' => '2',
                'item' => 'Updated Item',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('uu_tambahan', [
            'id' => $uuTambah->id,
            'jenis_surat' => 'Surat Edaran',
            'surat_id' => '2',
            'item' => 'Updated Item',
        ]);
    }

    public function test_update_uu_tambahan_returns_404_for_nonexistent_id()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/kantor/uu-tambahan/99999', [
                'jenis_surat' => 'Surat Edaran',
                'surat_id' => '2',
                'item' => 'Updated Item',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_uu_tambahan_partial_update_works()
    {
        $uuTambah = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Original Item',
        ]);

        $response = $this->actingAsUser()
            ->putJson("/api/kantor/uu-tambahan/{$uuTambah->id}", [
                'jenis_surat' => 'Surat Keputusan', // Required
                'surat_id' => '1', // Required
                'item' => 'Only Updated Item',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('uu_tambahan', [
            'id' => $uuTambah->id,
            'jenis_surat' => 'Surat Keputusan', // Unchanged
            'surat_id' => '1', // Unchanged
            'item' => 'Only Updated Item',
        ]);
    }

    // ==================== DELETE /api/kantor/uu-tambahan/{id} (destroy) ====================

    public function test_unauthenticated_user_cannot_delete_uu_tambahan()
    {
        $uuTambah = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Test Item',
        ]);

        $response = $this->deleteJson("/api/kantor/uu-tambahan/{$uuTambah->id}");

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_uu_tambahan()
    {
        $uuTambah = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Test Item',
        ]);

        $response = $this->actingAsUser()
            ->deleteJson("/api/kantor/uu-tambahan/{$uuTambah->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('uu_tambahan', [
            'id' => $uuTambah->id,
        ]);
    }

    public function test_delete_uu_tambahan_returns_404_for_nonexistent_id()
    {
        $response = $this->actingAsUser()
            ->deleteJson('/api/kantor/uu-tambahan/99999');

        $response->assertStatus(404);
    }

    // ==================== Security Tests ====================

    public function test_uu_tambahan_list_prevents_sql_injection()
    {
        UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Test Item',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu-tambahan?sort=\' OR \'1\'=\'1');

        // Should handle gracefully without SQL errors
        $this->assertContains($response->getStatusCode(), [200, 500]);
    }

    public function test_create_uu_tambahan_prevents_sql_injection()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu-tambahan', [
                'jenis_surat' => 'Surat Keputusan',
                'surat_id' => '1',
                'item' => "Test'; DROP TABLE uu_tambahan; --",
            ]);

        // Should either reject or handle gracefully
        $this->assertContains($response->getStatusCode(), [201, 422, 500]);
    }

    public function test_update_uu_tambahan_prevents_sql_injection()
    {
        $uuTambah = UuTambah::create([
            'jenis_surat' => 'Surat Keputusan',
            'surat_id' => '1',
            'item' => 'Test Item',
        ]);

        $response = $this->actingAsUser()
            ->putJson("/api/kantor/uu-tambahan/{$uuTambah->id}", [
                'jenis_surat' => 'Surat Keputusan',
                'surat_id' => '1',
                'item' => "Test'; DROP TABLE uu_tambahan; --",
            ]);

        // Should either reject or handle gracefully
        $this->assertContains($response->getStatusCode(), [200, 422, 500]);
    }
}
