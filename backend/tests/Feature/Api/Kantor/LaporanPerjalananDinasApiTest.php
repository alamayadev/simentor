<?php

namespace Tests\Feature\Api\Kantor;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\LaporanPerjalananDinas;
use App\Models\LaporanPerjalananDinasDetail;
use App\Models\LaporanPerjalananDinasDokumentasi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LaporanPerjalananDinasApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup user for authentication
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_laporan_perjalanan_dinas()
    {
        LaporanPerjalananDinas::factory()->count(3)->create();

        $response = $this->getJson('/api/kantor/laporan-perjalanan-dinas');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_create_laporan_perjalanan_dinas()
    {
        $data = [
            'nama_traveler' => 'Test Traveler',
            'tujuan' => 'Test Destination',
            'lama_tanggal' => '2 Hari',
            'dalam_rangka' => 'Test Purpose',
            'pembebanan' => 'DIPA',
            'status' => 'draft',
        ];

        $response = $this->postJson('/api/kantor/laporan-perjalanan-dinas', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nama_traveler' => 'Test Traveler']);
        
        $this->assertDatabaseHas('laporan_perjalanan_dinas', ['nama_traveler' => 'Test Traveler']);
    }

    public function test_can_show_laporan_perjalanan_dinas()
    {
        $laporan = LaporanPerjalananDinas::factory()->create();

        $response = $this->getJson("/api/kantor/laporan-perjalanan-dinas/{$laporan->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $laporan->id]);
    }

    public function test_can_update_laporan_perjalanan_dinas()
    {
        $laporan = LaporanPerjalananDinas::factory()->create();

        $updateData = ['nama_traveler' => 'Updated Name'];

        $response = $this->putJson("/api/kantor/laporan-perjalanan-dinas/{$laporan->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nama_traveler' => 'Updated Name']);

        $this->assertDatabaseHas('laporan_perjalanan_dinas', ['id' => $laporan->id, 'nama_traveler' => 'Updated Name']);
    }

    public function test_can_delete_laporan_perjalanan_dinas_with_cascading_file_deletion()
    {
        Storage::fake('direct');

        $laporan = LaporanPerjalananDinas::factory()->create();
        
        // Upload a file first
        $file = UploadedFile::fake()->image('photo.jpg');
        $path = $file->storeAs('laperdin', 'photo.jpg', 'direct');

        $laporan->dokumentasi()->create([
            'file_path' => 'laperdin/photo.jpg',
            'deskripsi' => 'Test Photo',
            'urutan' => 1
        ]);

        // Verify file exists on fake disk
        Storage::disk('direct')->assertExists('laperdin/photo.jpg');

        // Delete the report
        $response = $this->deleteJson("/api/kantor/laporan-perjalanan-dinas/{$laporan->id}");

        $response->assertStatus(200);

        // Verify report is deleted from DB
        $this->assertDatabaseMissing('laporan_perjalanan_dinas', ['id' => $laporan->id]);
        
        // Verify file is deleted from disk using cascading delete logic
        Storage::disk('direct')->assertMissing('laperdin/photo.jpg');
    }

    // Detail Tests
    public function test_can_add_detail()
    {
        $laporan = LaporanPerjalananDinas::factory()->create();
        $data = [
            'tanggal' => '2024-01-01',
            'uraian_lhp' => 'Activity 1',
            'kendala' => 'Issue 1',
            'solusi' => 'Fix 1'
        ];

        $response = $this->postJson("/api/kantor/laporan-perjalanan-dinas/{$laporan->id}/details", $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['uraian_lhp' => 'Activity 1']);
    }

    // Documentation Tests
    public function test_can_upload_documentation_to_public_folder()
    {
        Storage::fake('direct');

        $laporan = LaporanPerjalananDinas::factory()->create();
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson("/api/kantor/laporan-perjalanan-dinas/{$laporan->id}/dokumentasi", [
            'file' => $file,
            'deskripsi' => 'Public Photo'
        ]);

        $response->assertStatus(201);
        
        $data = $response->json('data');
        $filePath = $data['file_path'];

        // Assert path format
        $this->assertStringStartsWith('laperdin/', $filePath);
        
        // Assert file exists in 'direct' disk (public folder)
        Storage::disk('direct')->assertExists($filePath);
    }

    public function test_can_update_documentation_and_delete_old_file()
    {
        Storage::fake('direct');

        $laporan = LaporanPerjalananDinas::factory()->create();
        
        // Initial setup
        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldFilename = 'laperdin/old_' . uniqid() . '.jpg';
        Storage::disk('direct')->put($oldFilename, $oldFile->getContent());

        $doc = $laporan->dokumentasi()->create([
            'file_path' => $oldFilename,
            'deskripsi' => 'Old Desc'
        ]);

        Storage::disk('direct')->assertExists($oldFilename);

        // Update with new file
        $newFile = UploadedFile::fake()->image('new.jpg');

        $response = $this->postJson("/api/kantor/laporan-perjalanan-dinas/{$laporan->id}/dokumentasi/{$doc->id}", [
            'file' => $newFile,
            'deskripsi' => 'New Desc'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['deskripsi' => 'New Desc']);

        $newPath = $response->json('data.file_path');

        // Assert new file exists
        Storage::disk('direct')->assertExists($newPath);
        
        // Assert old file deleted
        Storage::disk('direct')->assertMissing($oldFilename);
    }

    public function test_can_generate_pdf()
    {
        $laporan = LaporanPerjalananDinas::factory()->create();
        
        // Add some details and doc to make it realistic
        LaporanPerjalananDinasDetail::factory()->count(2)->create(['laporan_perjalanan_dinas_id' => $laporan->id]);
        LaporanPerjalananDinasDokumentasi::factory()->count(1)->create(['laporan_perjalanan_dinas_id' => $laporan->id]);

        $response = $this->get("/api/kantor/laporan-perjalanan-dinas/{$laporan->id}/pdf");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
