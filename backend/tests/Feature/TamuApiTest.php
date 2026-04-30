<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tamu;

class TamuApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    public function test_can_list_tamu()
    {
        // Create some tamu records
        Tamu::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/tamu');

        $response->assertStatus(200);
        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_can_create_tamu_without_authentication()
    {
        $tamuData = [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_hp' => '081234567890',
            'asal_instansi' => 'BPS',
            'tgl_kunjungan' => '2023-01-01',
            'tujuan_kunjungan' => 'Konsultasi',
            'jenis_layanan' => 'Informasi',
            'detil_layanan' => 'Meminta informasi tentang kegiatan sensus'
        ];

        $response = $this->postJson('/api/kantor/tamu', $tamuData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'nama',
                    'email',
                    'no_hp',
                    'asal_instansi',
                    'tgl_kunjungan',
                    'tujuan_kunjungan',
                    'jenis_layanan',
                    'detil_layanan',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('tamu', [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com'
        ]);
    }

    public function test_can_show_tamu()
    {
        $tamu = Tamu::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/kantor/tamu/{$tamu->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $tamu->id,
                    'nama' => $tamu->nama,
                    'email' => $tamu->email
                ]
            ]);
    }

    public function test_can_update_tamu()
    {
        $tamu = Tamu::factory()->create();

        $updatedData = [
            'nama' => 'Budi Santoso Updated',
            'email' => 'budi_updated@example.com'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/kantor/tamu/{$tamu->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tamu updated successfully'
            ]);

        $this->assertDatabaseHas('tamu', [
            'id' => $tamu->id,
            'nama' => 'Budi Santoso Updated',
            'email' => 'budi_updated@example.com'
        ]);
    }

    public function test_can_delete_tamu()
    {
        $tamu = Tamu::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/kantor/tamu/{$tamu->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tamu deleted successfully'
            ]);

        $this->assertDatabaseMissing('tamu', [
            'id' => $tamu->id
        ]);
    }

    public function test_cannot_access_tamu_endpoints_without_authentication()
    {
        // Test that index endpoint requires authentication
        $response = $this->getJson('/api/kantor/tamu');
        $response->assertStatus(401);

        // Test that show endpoint requires authentication
        $tamu = Tamu::factory()->create();
        $response = $this->getJson("/api/kantor/tamu/{$tamu->id}");
        $response->assertStatus(401);

        // Test that update endpoint requires authentication
        $response = $this->putJson("/api/kantor/tamu/{$tamu->id}", []);
        $response->assertStatus(401);

        // Test that delete endpoint requires authentication
        $response = $this->deleteJson("/api/kantor/tamu/{$tamu->id}");
        $response->assertStatus(401);
    }

    public function test_store_endpoint_does_not_require_authentication()
    {
        $tamuData = [
            'nama' => 'Public User',
            'email' => 'public@example.com',
            'no_hp' => '081234567890',
            'asal_instansi' => 'Public Institution',
            'tgl_kunjungan' => '2023-01-01',
            'tujuan_kunjungan' => 'Information',
            'jenis_layanan' => 'Consultation',
            'detil_layanan' => 'Need help with something'
        ];

        $response = $this->postJson('/api/kantor/tamu', $tamuData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Tamu created successfully'
            ]);
    }
}
