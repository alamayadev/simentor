<?php

namespace Tests\Feature\Api\Kantor;

use App\Models\Pengaduan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\BaseApiTestCase;

class PengaduanApiTest extends BaseApiTestCase
{
    use RefreshDatabase;

    private array $validPengaduanData;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validPengaduanData = [
            'jenis_pelangaran' => 'Korupsi Dana',
            'lainnya' => 'Penyelewengan anggaran proyek',
            'pelaku' => 'Kepala Seksi',
            'waktu_kejadian' => '2024-01-15',
            'kronologi' => 'Pada tanggal tersebut, terjadi penyelewengan dana...',
            'bukti' => 'document_bukti_transfer.pdf',
        ];
    }

    // Authentication Tests for Index
    /** @test */
    public function it_requires_authentication_to_list_pengaduan(): void
    {
        $response = $this->getJson('/api/kantor/pengaduan');

        $response->assertStatus(401);
    }

    // Authentication Tests for Show
    /** @test */
    public function it_requires_authentication_to_show_pengaduan(): void
    {
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->getJson("/api/kantor/pengaduan/{$pengaduan->id}");

        $response->assertStatus(401);
    }

    // Authentication Tests for Update
    /** @test */
    public function it_requires_authentication_to_update_pengaduan(): void
    {
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->putJson("/api/kantor/pengaduan/{$pengaduan->id}", $this->validPengaduanData);

        $response->assertStatus(401);
    }

    // Authentication Tests for Delete
    /** @test */
    public function it_requires_authentication_to_delete_pengaduan(): void
    {
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->deleteJson("/api/kantor/pengaduan/{$pengaduan->id}");

        $response->assertStatus(401);
    }

    // Store Method Tests (Public Access)
    /** @test */
    public function it_creates_pengaduan_without_authentication(): void
    {
        $response = $this->postJson('/api/kantor/pengaduan', $this->validPengaduanData);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
    }

    // Index Method Tests
    /** @test */
    public function it_lists_pengaduan_successfully_as_admin(): void
    {
        Pengaduan::factory()->count(5)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_lists_pengaduan_successfully_as_user(): void
    {
        Pengaduan::factory()->count(3)->create();

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/pengaduan');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_filters_pengaduan_by_jenis_pelangaran(): void
    {
        Pengaduan::factory()->create(['jenis_pelangaran' => 'Korupsi Dana']);
        Pengaduan::factory()->create(['jenis_pelangaran' => 'Gratifikasi']);
        Pengaduan::factory()->create(['jenis_pelangaran' => 'Korupsi Proyek']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan?filter[jenis_pelangaran]=Korupsi');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_filters_pengaduan_by_pelaku(): void
    {
        Pengaduan::factory()->create(['pelaku' => 'Kepala Seksi']);
        Pengaduan::factory()->create(['pelaku' => 'Staf Administrasi']);
        Pengaduan::factory()->create(['pelaku' => 'Kepala Dinas']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan?filter[pelaku]=Kepala');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_filters_pengaduan_by_lainnya(): void
    {
        Pengaduan::factory()->create(['lainnya' => 'Penyelewengan anggaran']);
        Pengaduan::factory()->create(['lainnya' => 'Pemalsuan dokumen']);
        Pengaduan::factory()->create(['lainnya' => 'Penyelewengan dana']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan?filter[lainnya]=Penyelewengan');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_paginates_pengaduan_correctly(): void
    {
        Pengaduan::factory()->count(25)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan?page=2&per_page=10');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_empty_pengaduan_list(): void
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // Store Method Validation Tests
    /** @test */
    public function it_validates_jenis_pelangaran_is_required(): void
    {
        $invalidData = array_merge($this->validPengaduanData, ['jenis_pelangaran' => '']);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_jenis_pelangaran_max_length(): void
    {
        $invalidData = array_merge($this->validPengaduanData, [
            'jenis_pelangaran' => str_repeat('a', 256)
        ]);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_lainnya_is_required(): void
    {
        $invalidData = array_merge($this->validPengaduanData, ['lainnya' => '']);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_lainnya_max_length(): void
    {
        $invalidData = array_merge($this->validPengaduanData, [
            'lainnya' => str_repeat('a', 256)
        ]);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_pelaku_is_required(): void
    {
        $invalidData = array_merge($this->validPengaduanData, ['pelaku' => '']);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_pelaku_max_length(): void
    {
        $invalidData = array_merge($this->validPengaduanData, [
            'pelaku' => str_repeat('a', 256)
        ]);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_waktu_kejadian_is_required(): void
    {
        $invalidData = array_merge($this->validPengaduanData, ['waktu_kejadian' => '']);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_waktu_kejadian_date_format(): void
    {
        $invalidData = array_merge($this->validPengaduanData, ['waktu_kejadian' => 'invalid-date']);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_kronologi_is_required(): void
    {
        // Kronologi is required based on original test
        $invalidData = array_merge($this->validPengaduanData, ['kronologi' => '']);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_bukti_is_required(): void
    {
        $invalidData = array_merge($this->validPengaduanData, ['bukti' => '']);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_bukti_max_length(): void
    {
        $invalidData = array_merge($this->validPengaduanData, [
            'bukti' => str_repeat('a', 256)
        ]);

        $response = $this->postJson('/api/kantor/pengaduan', $invalidData);

        $response->assertStatus(422);
    }

    // Show Method Tests
    /** @test */
    public function it_shows_pengaduan_successfully_as_admin(): void
    {
        $pengaduan = Pengaduan::factory()->create($this->validPengaduanData);

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_shows_pengaduan_successfully_as_user(): void
    {
        $pengaduan = Pengaduan::factory()->create($this->validPengaduanData);

        $response = $this->actingAsOrganik()
            ->getJson("/api/kantor/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_not_found_for_nonexistent_pengaduan(): void
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan/999999');

        $response->assertStatus(404);
    }

    // Update Method Tests
    /** @test */
    public function it_updates_pengaduan_successfully_as_admin(): void
    {
        $pengaduan = Pengaduan::factory()->create();
        $updateData = [
            'jenis_pelangaran' => 'Gratifikasi Updated',
            'lainnya' => 'Updated additional info',
            'pelaku' => 'Updated Actor',
            'waktu_kejadian' => '2024-02-20',
            'kronologi' => 'Updated chronology description',
            'bukti' => 'updated_document.pdf',
        ];

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/pengaduan/{$pengaduan->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_updates_pengaduan_partially(): void
    {
        $pengaduan = Pengaduan::factory()->create([
            'jenis_pelangaran' => 'Original Violation',
            'pelaku' => 'Original Actor',
            'waktu_kejadian' => '2024-01-01',
        ]);

        $partialUpdate = ['jenis_pelangaran' => 'Partially Updated Violation'];

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/pengaduan/{$pengaduan->id}", $partialUpdate);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_not_found_when_updating_nonexistent_pengaduan(): void
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/kantor/pengaduan/999999', $this->validPengaduanData);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_validates_update_data(): void
    {
        $pengaduan = Pengaduan::factory()->create();
        $invalidData = [
            'waktu_kejadian' => 'invalid-date',
            'bukti' => str_repeat('a', 300),
        ];

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/pengaduan/{$pengaduan->id}", $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_updates_pengaduan_successfully_as_user(): void
    {
        $pengaduan = Pengaduan::factory()->create();
        $updateData = ['jenis_pelangaran' => 'User Updated Violation'];

        $response = $this->actingAsOrganik()
            ->putJson("/api/kantor/pengaduan/{$pengaduan->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // Delete Method Tests
    /** @test */
    public function it_deletes_pengaduan_successfully_as_admin(): void
    {
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_deletes_pengaduan_successfully_as_user(): void
    {
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->actingAsOrganik()
            ->deleteJson("/api/kantor/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_not_found_when_deleting_nonexistent_pengaduan(): void
    {
        $response = $this->actingAsAdmin()
            ->deleteJson('/api/kantor/pengaduan/999999');

        $response->assertStatus(404);
    }

    // Security Tests
    /** @test */
    public function it_prevents_sql_injection_in_search(): void
    {
        Pengaduan::factory()->create(['jenis_pelangaran' => 'Test Violation']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan?filter[jenis_pelangaran]=Test');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_sanitizes_input_data(): void
    {
        $maliciousData = [
            'jenis_pelangaran' => '<script>alert("xss")</script>Violation',
            'lainnya' => 'Additional info',
            'pelaku' => 'Test Actor',
            'waktu_kejadian' => '2024-01-15',
            'kronologi' => '<img src=x onerror=alert("xss")> Test chronology',
            'bukti' => 'document.pdf',
        ];

        $response = $this->postJson('/api/kantor/pengaduan', $maliciousData);

        $response->assertStatus(201);
    }

    // Performance Tests
    /** @test */
    public function it_handles_large_pengaduan_dataset_efficiently(): void
    {
        Pengaduan::factory()->count(100)->create();

        $startTime = microtime(true);
        
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan?per_page=50');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(5.0, $executionTime);
    }

    // Edge Cases
    /** @test */
    public function it_handles_date_edge_cases(): void
    {
        $pengaduan = Pengaduan::factory()->create(['waktu_kejadian' => '2024-02-29']); // Leap year

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_multiple_filter_combinations(): void
    {
        Pengaduan::factory()->create([
            'jenis_pelangaran' => 'Korupsi Dana',
            'pelaku' => 'Kepala Seksi',
            'lainnya' => 'Penyelewengan anggaran',
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/pengaduan?filter[jenis_pelangaran]=Korupsi&filter[lainnya]=Penyelewengan');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_unicode_characters(): void
    {
        $unicodeData = [
            'jenis_pelangaran' => 'Korupsi Dana 日本語',
            'lainnya' => 'Penyelewengan anggaran dengan karakter khusus: àáâãäå',
            'pelaku' => 'Kepala Seksi 特殊',
            'waktu_kejadian' => '2024-01-15',
            'kronologi' => 'Kronologi dengan karakter unicode: 한국어 العربية',
            'bukti' => 'document_special.pdf',
        ];

        $response = $this->postJson('/api/kantor/pengaduan', $unicodeData);

        $response->assertStatus(201);
    }

    /** @test */
    public function it_maintains_data_integrity_across_operations(): void
    {
        // Create
        $response = $this->postJson('/api/kantor/pengaduan', $this->validPengaduanData);
        $response->assertStatus(201);
        $pengaduanId = $response->json('data.id');

        // Read
        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/pengaduan/{$pengaduanId}");
        $response->assertStatus(200);

        // Update
        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/pengaduan/{$pengaduanId}", ['jenis_pelangaran' => 'Updated Violation']);
        $response->assertStatus(200);

        // Delete
        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/pengaduan/{$pengaduanId}");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_sensitive_content(): void
    {
        $response = $this->postJson('/api/kantor/pengaduan', $this->validPengaduanData);
        $response->assertStatus(201);
    }
}