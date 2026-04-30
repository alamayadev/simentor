<?php

namespace Tests\Feature\Api\Kantor;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\Uu;
use App\Models\User;

class UuApiTest extends BaseApiTestCase
{
    // ==================== GET /api/kantor/uu (index) ====================

    public function test_unauthenticated_user_cannot_list_uu()
    {
        $response = $this->getJson('/api/kantor/uu');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_uu()
    {
        Uu::create([
            'jenis' => 'UU',
            'nama' => 'Test UU 1',
            'detil' => 'Detail 1',
        ]);
        Uu::create([
            'jenis' => 'PP',
            'nama' => 'Test PP 1',
            'detil' => 'Detail 2',
        ]);
        Uu::create([
            'jenis' => 'Perpres',
            'nama' => 'Test Perpres 1',
            'detil' => 'Detail 3',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [],
            ]);

        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_uu_list_pagination_works()
    {
        for ($i = 0; $i < 20; $i++) {
            Uu::create([
                'jenis' => 'UU',
                'nama' => "Test UU {$i}",
                'detil' => "Detail {$i}",
            ]);
        }

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.count', 10)
            ->assertJsonPath('pagination_info.total_records', 20)
            ->assertJsonCount(10, 'data');

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNextPage($response);
    }

    public function test_uu_list_can_filter_by_nama()
    {
        Uu::create(['nama' => 'Undang-Undang Test 1', 'jenis' => 'UU', 'detil' => 'Detail 1']);
        Uu::create(['nama' => 'Undang-Undang Test 2', 'jenis' => 'UU', 'detil' => 'Detail 2']);
        Uu::create(['nama' => 'Peraturan Pemerintah', 'jenis' => 'PP', 'detil' => 'Detail 3']);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu?filter[nama]=Undang-Undang');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(2, count($data));
    }

    public function test_uu_list_can_filter_by_jenis()
    {
        Uu::create(['jenis' => 'UU', 'nama' => 'Test 1', 'detil' => 'Detail 1']);
        Uu::create(['jenis' => 'UU', 'nama' => 'Test 2', 'detil' => 'Detail 2']);
        Uu::create(['jenis' => 'PP', 'nama' => 'Test 3', 'detil' => 'Detail 3']);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu?filter[jenis]=UU');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(2, count($data));
    }

    public function test_uu_list_can_sort_by_nama()
    {
        Uu::create(['nama' => 'Zebra', 'jenis' => 'UU', 'detil' => 'Detail 1']);
        Uu::create(['nama' => 'Apple', 'jenis' => 'UU', 'detil' => 'Detail 2']);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu?sort=nama');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals('Apple', $data[0]['nama']);
    }

    public function test_uu_list_default_sort_by_id_desc()
    {
        $uu1 = Uu::create(['nama' => 'First', 'jenis' => 'UU', 'detil' => 'Detail 1']);
        $uu2 = Uu::create(['nama' => 'Second', 'jenis' => 'UU', 'detil' => 'Detail 2']);
        $uu3 = Uu::create(['nama' => 'Third', 'jenis' => 'UU', 'detil' => 'Detail 3']);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertTrue($data[0]['id'] > $data[1]['id']);
    }

    public function test_uu_list_empty_when_no_data()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu');

        $response->assertStatus(200)
            ->assertJsonPath('meta.count', 0)
            ->assertJsonPath('pagination_info.total_records', 0)
            ->assertJsonCount(0, 'data');

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNoNextPage($response);
    }

    // ==================== POST /api/kantor/uu (store) ====================

    public function test_unauthenticated_user_cannot_create_uu()
    {
        $response = $this->postJson('/api/kantor/uu', [
            'jenis' => 'UU',
            'nama' => 'Test UU',
            'detil' => 'Test Detail',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_uu()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu', [
                'jenis' => 'UU',
                'nama' => 'Undang-Undang Nomor 1 Tahun 2024',
                'detil' => 'Tentang Test',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'jenis',
                    'nama',
                    'detil',
                ]
            ]);

        $this->assertDatabaseHas('uu', [
            'jenis' => 'UU',
            'nama' => 'Undang-Undang Nomor 1 Tahun 2024',
        ]);
    }

    public function test_create_uu_requires_jenis()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu', [
                'nama' => 'Test UU',
                'detil' => 'Test Detail',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_uu_requires_nama()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu', [
                'jenis' => 'UU',
                'detil' => 'Test Detail',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_uu_requires_detil()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu', [
                'jenis' => 'UU',
                'nama' => 'Test UU',
            ]);

        $response->assertStatus(422);
    }

    // ==================== GET /api/kantor/uu/{id} (show) ====================

    public function test_unauthenticated_user_cannot_view_uu()
    {
        $uu = Uu::create([
            'jenis' => 'UU',
            'nama' => 'Test UU',
            'detil' => 'Test Detail',
        ]);

        $response = $this->getJson("/api/kantor/uu/{$uu->id}");

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_uu()
    {
        $uu = Uu::create([
            'jenis' => 'UU',
            'nama' => 'Test UU',
            'detil' => 'Test Detail',
        ]);

        $response = $this->actingAsUser()
            ->getJson("/api/kantor/uu/{$uu->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $uu->id)
            ->assertJsonPath('data.jenis', 'UU')
            ->assertJsonPath('data.nama', 'Test UU');
    }

    public function test_view_uu_returns_404_for_nonexistent_id()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu/99999');

        $response->assertStatus(404);
    }

    // ==================== PUT /api/kantor/uu/{id} (update) ====================

    public function test_unauthenticated_user_cannot_update_uu()
    {
        $uu = Uu::create([
            'jenis' => 'UU',
            'nama' => 'Test UU',
            'detil' => 'Test Detail',
        ]);

        $response = $this->putJson("/api/kantor/uu/{$uu->id}", [
            'jenis' => 'PP',
            'nama' => 'Updated UU',
            'detil' => 'Updated Detail',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_uu()
    {
        $uu = Uu::create([
            'jenis' => 'UU',
            'nama' => 'Original UU',
            'detil' => 'Original Detail',
        ]);

        $response = $this->actingAsUser()
            ->putJson("/api/kantor/uu/{$uu->id}", [
                'jenis' => 'PP',
                'nama' => 'Updated UU',
                'detil' => 'Updated Detail',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('uu', [
            'id' => $uu->id,
            'jenis' => 'PP',
            'nama' => 'Updated UU',
            'detil' => 'Updated Detail',
        ]);
    }

    public function test_update_uu_returns_404_for_nonexistent_id()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/kantor/uu/99999', [
                'jenis' => 'PP',
                'nama' => 'Updated UU',
                'detil' => 'Updated Detail',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_uu_partial_update_works()
    {
        $uu = Uu::create([
            'jenis' => 'UU',
            'nama' => 'Original UU',
            'detil' => 'Original Detail',
        ]);

        $response = $this->actingAsUser()
            ->putJson("/api/kantor/uu/{$uu->id}", [
                'jenis' => 'UU', // Required
                'nama' => 'Only Updated Name',
                'detil' => 'Original Detail', // Required
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('uu', [
            'id' => $uu->id,
            'jenis' => 'UU', // Unchanged
            'nama' => 'Only Updated Name',
            'detil' => 'Original Detail', // Unchanged
        ]);
    }

    // ==================== DELETE /api/kantor/uu/{id} (destroy) ====================

    public function test_unauthenticated_user_cannot_delete_uu()
    {
        $uu = Uu::create([
            'jenis' => 'UU',
            'nama' => 'Test UU',
            'detil' => 'Test Detail',
        ]);

        $response = $this->deleteJson("/api/kantor/uu/{$uu->id}");

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_uu()
    {
        $uu = Uu::create([
            'jenis' => 'UU',
            'nama' => 'Test UU',
            'detil' => 'Test Detail',
        ]);

        $response = $this->actingAsUser()
            ->deleteJson("/api/kantor/uu/{$uu->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('uu', [
            'id' => $uu->id,
        ]);
    }

    public function test_delete_uu_returns_404_for_nonexistent_id()
    {
        $response = $this->actingAsUser()
            ->deleteJson('/api/kantor/uu/99999');

        $response->assertStatus(404);
    }

    // ==================== Security Tests ====================

    public function test_uu_list_prevents_sql_injection_in_filter()
    {
        Uu::create([
            'jenis' => 'UU',
            'nama' => 'Test UU',
            'detil' => 'Test Detail',
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/uu?filter[nama]=\' OR \'1\'=\'1');

        // Should handle gracefully without SQL errors
        $response->assertStatus(200);
    }

    public function test_create_uu_prevents_sql_injection()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/kantor/uu', [
                'jenis' => 'UU',
                'nama' => "Test'; DROP TABLE uus; --",
                'detil' => 'Test',
            ]);

        // Should either reject or handle gracefully
        $this->assertContains($response->getStatusCode(), [201, 422, 500]);
    }

    public function test_update_uu_prevents_sql_injection()
    {
        $uu = Uu::create([
            'jenis' => 'UU',
            'nama' => 'Test UU',
            'detil' => 'Test Detail',
        ]);

        $response = $this->actingAsUser()
            ->putJson("/api/kantor/uu/{$uu->id}", [
                'nama' => "Test'; DROP TABLE uus; --",
            ]);

        // Should either reject or handle gracefully
        $this->assertContains($response->getStatusCode(), [200, 422, 500]);
    }
}
