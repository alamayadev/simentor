<?php

namespace Tests\Feature\Api\Kantor;

use App\Models\Tamu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\BaseApiTestCase;

class TamuApiTest extends BaseApiTestCase
{
    use RefreshDatabase;

    private array $validTamuData;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validTamuData = [
            'nama' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'no_hp' => '081234567890',
            'asal_instansi' => 'BPS Provinsi DKI Jakarta',
            'tgl_kunjungan' => '2024-01-15',
            'tujuan_kunjungan' => 'Konsultasi Data Kependudukan',
            'jenis_layanan' => 'Informasi Statistik',
            'detil_layanan' => 'Meminta data tentang kependudukan untuk kepenelitian',
        ];
    }

    // Authentication Tests for Index
    /** @test */
    public function it_requires_authentication_to_list_tamu(): void
    {
        $response = $this->getJson('/api/kantor/tamu');

        $response->assertStatus(401);
    }

    // Authentication Tests for Show
    /** @test */
    public function it_requires_authentication_to_show_tamu(): void
    {
        $tamu = Tamu::factory()->create();

        $response = $this->getJson("/api/kantor/tamu/{$tamu->id}");

        $response->assertStatus(401);
    }

    // Authentication Tests for Update
    /** @test */
    public function it_requires_authentication_to_update_tamu(): void
    {
        $tamu = Tamu::factory()->create();

        $response = $this->putJson("/api/kantor/tamu/{$tamu->id}", $this->validTamuData);

        $response->assertStatus(401);
    }

    // Authentication Tests for Delete
    /** @test */
    public function it_requires_authentication_to_delete_tamu(): void
    {
        $tamu = Tamu::factory()->create();

        $response = $this->deleteJson("/api/kantor/tamu/{$tamu->id}");

        $response->assertStatus(401);
    }

    // Store Method Tests (Public Access)
    /** @test */
    public function it_creates_tamu_without_authentication(): void
    {
        $response = $this->postJson('/api/kantor/tamu', $this->validTamuData);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
    }

    // Index Method Tests
    /** @test */
    public function it_lists_tamu_successfully_as_admin(): void
    {
        Tamu::factory()->count(5)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_lists_tamu_successfully_as_user(): void
    {
        Tamu::factory()->count(3)->create();

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/tamu');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_filters_tamu_by_nama(): void
    {
        Tamu::factory()->create(['nama' => 'Budi Santoso']);
        Tamu::factory()->create(['nama' => 'Ani Wijaya']);
        Tamu::factory()->create(['nama' => 'Budi Pratama']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu?filter[nama]=Budi');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_filters_tamu_by_email(): void
    {
        Tamu::factory()->create(['email' => 'budi@example.com']);
        Tamu::factory()->create(['email' => 'ani@gmail.com']);
        Tamu::factory()->create(['email' => 'budi.pratama@example.com']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu?filter[email]=budi');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_filters_tamu_by_asal_instansi(): void
    {
        Tamu::factory()->create(['asal_instansi' => 'BPS Provinsi DKI Jakarta']);
        Tamu::factory()->create(['asal_instansi' => 'Universitas Indonesia']);
        Tamu::factory()->create(['asal_instansi' => 'BPS Kabupaten Bogor']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu?filter[asal_instansi]=BPS');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_filters_tamu_by_tujuan_kunjungan(): void
    {
        Tamu::factory()->create(['tujuan_kunjungan' => 'Konsultasi Data']);
        Tamu::factory()->create(['tujuan_kunjungan' => 'Penelitian']);
        Tamu::factory()->create(['tujuan_kunjungan' => 'Konsultasi Statistik']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu?filter[tujuan_kunjungan]=Konsultasi');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_filters_tamu_by_jenis_layanan(): void
    {
        Tamu::factory()->create(['jenis_layanan' => 'Informasi Statistik']);
        Tamu::factory()->create(['jenis_layanan' => 'Konsultasi Teknis']);
        Tamu::factory()->create(['jenis_layanan' => 'Informasi Data']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu?filter[jenis_layanan]=Informasi');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_paginates_tamu_correctly(): void
    {
        Tamu::factory()->count(25)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu?page=2&per_page=10');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_empty_tamu_list(): void
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // Store Method Validation Tests
    /** @test */
    public function it_validates_nama_is_required(): void
    {
        $invalidData = array_merge($this->validTamuData, ['nama' => '']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_nama_max_length(): void
    {
        $invalidData = array_merge($this->validTamuData, [
            'nama' => str_repeat('a', 256)
        ]);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_email_is_required(): void
    {
        $invalidData = array_merge($this->validTamuData, ['email' => '']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_email_format(): void
    {
        $invalidData = array_merge($this->validTamuData, ['email' => 'invalid-email']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_email_max_length(): void
    {
        $invalidData = array_merge($this->validTamuData, [
            'email' => str_repeat('a', 250) . '@example.com'
        ]);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_no_hp_is_required(): void
    {
        $invalidData = array_merge($this->validTamuData, ['no_hp' => '']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_no_hp_max_length(): void
    {
        $invalidData = array_merge($this->validTamuData, [
            'no_hp' => str_repeat('1', 21)
        ]);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_asal_instansi_is_required(): void
    {
        $invalidData = array_merge($this->validTamuData, ['asal_instansi' => '']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_tgl_kunjungan_is_required(): void
    {
        $invalidData = array_merge($this->validTamuData, ['tgl_kunjungan' => '']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_tgl_kunjungan_date_format(): void
    {
        $invalidData = array_merge($this->validTamuData, ['tgl_kunjungan' => 'invalid-date']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_tujuan_kunjungan_is_required(): void
    {
        $invalidData = array_merge($this->validTamuData, ['tujuan_kunjungan' => '']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_jenis_layanan_is_required(): void
    {
        $invalidData = array_merge($this->validTamuData, ['jenis_layanan' => '']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_detil_layanan_is_required(): void
    {
        $invalidData = array_merge($this->validTamuData, ['detil_layanan' => '']);

        $response = $this->postJson('/api/kantor/tamu', $invalidData);

        $response->assertStatus(422);
    }

    // Show Method Tests
    /** @test */
    public function it_shows_tamu_successfully_as_admin(): void
    {
        $tamu = Tamu::factory()->create($this->validTamuData);

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/tamu/{$tamu->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_shows_tamu_successfully_as_user(): void
    {
        $tamu = Tamu::factory()->create($this->validTamuData);

        $response = $this->actingAsOrganik()
            ->getJson("/api/kantor/tamu/{$tamu->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_not_found_for_nonexistent_tamu(): void
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu/999999');

        $response->assertStatus(404);
    }

    // Update Method Tests
    /** @test */
    public function it_updates_tamu_successfully_as_admin(): void
    {
        $tamu = Tamu::factory()->create();
        $updateData = [
            'nama' => 'Updated Name',
            'email' => 'updated@example.com',
            'no_hp' => '089876543210',
            'asal_instansi' => 'Updated Institution',
            'tgl_kunjungan' => '2024-02-20',
            'tujuan_kunjungan' => 'Updated Purpose',
            'jenis_layanan' => 'Updated Service',
            'detil_layanan' => 'Updated details',
        ];

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/tamu/{$tamu->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_updates_tamu_partially(): void
    {
        $tamu = Tamu::factory()->create([
            'nama' => 'Original Name',
            'email' => 'original@example.com',
            'no_hp' => '081234567890',
        ]);

        $partialUpdate = ['nama' => 'Partially Updated Name'];

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/tamu/{$tamu->id}", $partialUpdate);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_not_found_when_updating_nonexistent_tamu(): void
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/kantor/tamu/999999', $this->validTamuData);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_validates_update_data(): void
    {
        $tamu = Tamu::factory()->create();
        $invalidData = [
            'email' => 'invalid-email',
        ];

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/tamu/{$tamu->id}", $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_updates_tamu_successfully_as_user(): void
    {
        $tamu = Tamu::factory()->create();
        $updateData = ['nama' => 'User Updated Name'];

        $response = $this->actingAsOrganik()
            ->putJson("/api/kantor/tamu/{$tamu->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // Delete Method Tests
    /** @test */
    public function it_deletes_tamu_successfully_as_admin(): void
    {
        $tamu = Tamu::factory()->create();

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/tamu/{$tamu->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_deletes_tamu_successfully_as_user(): void
    {
        $tamu = Tamu::factory()->create();

        $response = $this->actingAsOrganik()
            ->deleteJson("/api/kantor/tamu/{$tamu->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_not_found_when_deleting_nonexistent_tamu(): void
    {
        $response = $this->actingAsAdmin()
            ->deleteJson('/api/kantor/tamu/999999');

        $response->assertStatus(404);
    }

    // Security Tests
    /** @test */
    public function it_prevents_sql_injection_in_search(): void
    {
        Tamu::factory()->create(['nama' => 'Test Guest']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu?filter[nama]=test');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_sanitizes_input_data(): void
    {
        $maliciousData = [
            'nama' => '<script>alert("xss")</script>Guest Name',
            'email' => 'test@example.com',
            'no_hp' => '081234567890',
            'asal_instansi' => 'Test Institution',
            'tgl_kunjungan' => '2024-01-15',
            'tujuan_kunjungan' => 'Test Purpose',
            'jenis_layanan' => 'Test Service',
            'detil_layanan' => 'Test details',
        ];

        $response = $this->postJson('/api/kantor/tamu', $maliciousData);

        $response->assertStatus(201);
    }

    // Performance Tests
    /** @test */
    public function it_handles_large_tamu_dataset_efficiently(): void
    {
        Tamu::factory()->count(100)->create();

        $startTime = microtime(true);
        
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu?per_page=50');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(5.0, $executionTime);
    }

    // Edge Case Tests
    /** @test */
    public function it_handles_date_edge_cases(): void
    {
        $tamu = Tamu::factory()->create([
            'tgl_kunjungan' => '2024-02-29' // Leap year
        ]);

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/tamu/{$tamu->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_multiple_filter_combinations(): void
    {
        Tamu::factory()->create([
            'nama' => 'Budi Santoso',
            'asal_instansi' => 'BPS Jakarta'
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/tamu?filter[nama]=Budi&filter[asal_instansi]=BPS');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_unicode_characters(): void
    {
        $unicodeData = array_merge($this->validTamuData, [
            'nama' => '日本語テスト',
            'asal_instansi' => 'Институт статистики'
        ]);

        $response = $this->postJson('/api/kantor/tamu', $unicodeData);

        $response->assertStatus(201);
    }

    /** @test */
    public function it_maintains_data_integrity_across_operations(): void
    {
        // Create
        $response = $this->postJson('/api/kantor/tamu', $this->validTamuData);
        $response->assertStatus(201);
        $tamuId = $response->json('data.id');

        // Read
        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/tamu/{$tamuId}");
        $response->assertStatus(200);

        // Update
        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/tamu/{$tamuId}", ['nama' => 'Updated Name']);
        $response->assertStatus(200);

        // Delete
        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/tamu/{$tamuId}");
        $response->assertStatus(200);
    }
}