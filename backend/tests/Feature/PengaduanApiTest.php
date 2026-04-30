<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pengaduan;

class PengaduanApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    public function test_can_list_pengaduan()
    {
        // Create some pengaduan records
        Pengaduan::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/pengaduan');

        $response->assertStatus(200);
        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_can_create_pengaduan_without_authentication()
    {
        $pengaduanData = [
            'jenis_pelangaran' => 'Korupsi',
            'lainnya' => 'Informasi tambahan',
            'pelaku' => 'Pegawai',
            'waktu_kejadian' => '2023-01-01',
            'kronologi' => 'Deskripsi kronologi kejadian',
            'bukti' => 'file_bukti.jpg'
        ];

        $response = $this->postJson('/api/kantor/pengaduan', $pengaduanData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'jenis_pelangaran',
                    'lainnya',
                    'pelaku',
                    'waktu_kejadian',
                    'kronologi',
                    'bukti',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('pengaduan', [
            'jenis_pelangaran' => 'Korupsi',
            'pelaku' => 'Pegawai'
        ]);
    }

    public function test_can_show_pengaduan()
    {
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/kantor/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully',
                'data' => [
                    'id' => $pengaduan->id,
                    'jenis_pelangaran' => $pengaduan->jenis_pelangaran,
                    'pelaku' => $pengaduan->pelaku
                ]
            ]);
    }

    public function test_can_update_pengaduan()
    {
        $pengaduan = Pengaduan::factory()->create();

        $updatedData = [
            'jenis_pelangaran' => 'Korupsi Updated',
            'pelaku' => 'Pegawai Updated'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/kantor/pengaduan/{$pengaduan->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pengaduan updated successfully'
            ]);

        $this->assertDatabaseHas('pengaduan', [
            'id' => $pengaduan->id,
            'jenis_pelangaran' => 'Korupsi Updated',
            'pelaku' => 'Pegawai Updated'
        ]);
    }

    public function test_can_delete_pengaduan()
    {
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/kantor/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pengaduan deleted successfully'
            ]);

        $this->assertDatabaseMissing('pengaduan', [
            'id' => $pengaduan->id
        ]);
    }

    public function test_cannot_access_pengaduan_endpoints_without_authentication()
    {
        // Test that index endpoint requires authentication
        $response = $this->getJson('/api/kantor/pengaduan');
        $response->assertStatus(401);

        // Test that show endpoint requires authentication
        $pengaduan = Pengaduan::factory()->create();
        $response = $this->getJson("/api/kantor/pengaduan/{$pengaduan->id}");
        $response->assertStatus(401);

        // Test that update endpoint requires authentication
        $response = $this->putJson("/api/kantor/pengaduan/{$pengaduan->id}", []);
        $response->assertStatus(401);

        // Test that delete endpoint requires authentication
        $response = $this->deleteJson("/api/kantor/pengaduan/{$pengaduan->id}");
        $response->assertStatus(401);
    }

    public function test_store_endpoint_does_not_require_authentication()
    {
        $pengaduanData = [
            'jenis_pelangaran' => 'Nepotisme',
            'lainnya' => 'Informasi tambahan 2',
            'pelaku' => 'Mitra',
            'waktu_kejadian' => '2023-02-01',
            'kronologi' => 'Deskripsi kronologi kejadian 2',
            'bukti' => 'file_bukti2.jpg'
        ];

        $response = $this->postJson('/api/kantor/pengaduan', $pengaduanData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Pengaduan created successfully'
            ]);
    }
}
