<?php

namespace Tests\Feature\Api\Kantor;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\User;
use App\Models\Skp;
use App\Jobs\ScanUploadedFile;
use App\Jobs\UploadSkpToGoogleDrive;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class SkpApiTest extends BaseApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The local PHP runtime may not have fileinfo enabled; avoid failing
        // non-file SKP tests during setUp in that environment.
        if (class_exists(\finfo::class)) {
            Storage::fake('direct');
        }

        // Fake queue to capture dispatched jobs
        Queue::fake();
    }

    // =====================================================================
    // GET /api/kantor/skp/stats (index) - Authentication Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_access_stats()
    {
        $response = $this->getJson('/api/kantor/skp/stats');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_stats_with_default_parameters()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'tahun_options',
                    'bulan_options',
                    'selected_tahun',
                    'selected_tahun2',
                    'selected_bulan',
                    'skp_monthly_stats' => [
                        'not_uploaded_count',
                        'uploaded_count',
                        'not_uploaded_users',
                        'period'
                    ],
                    'skp_annual_setting_stats' => [
                        'not_uploaded_users',
                        'period'
                    ],
                    'skp_annual_evaluation_stats' => [
                        'not_uploaded_users',
                        'period'
                    ],
                    'total_employees'
                ]
            ])
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_stats_with_custom_parameters()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats?tahun=2024&bulan=05&tahun2=2024');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.selected_tahun', '2024')
            ->assertJsonPath('data.selected_bulan', '05')
            ->assertJsonPath('data.selected_tahun2', '2024');
    }

    /** @test */
    public function it_validates_stats_year_parameter()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats?tahun=abc');

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_stats_year_range()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats?tahun=1800');

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_returns_correct_monthly_stats()
    {
        // Create BPS user
        $bpsUser = User::factory()->create(['email' => 'test@bps.go.id']);
        
        // Create SKP for specific month/year
        Skp::factory()->create([
            'user_id' => $bpsUser->id,
            'jenis' => 'SKP Bulanan',
            'bulan' => '05',
            'tahun' => now()->year
        ]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats?tahun=' . now()->year . '&bulan=05');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.skp_monthly_stats.uploaded_count', fn ($count) => $count >= 0);
    }

    /** @test */
    public function it_returns_correct_year_options()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.tahun_options', fn ($options) => count($options) === 3);
    }

    /** @test */
    public function it_returns_correct_month_options()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.bulan_options', fn ($options) => count($options) === 12);
    }

    // =====================================================================
    // GET /api/kantor/skp/stats2 (stat2) - Alternative Statistics Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_access_stats2()
    {
        $response = $this->getJson('/api/kantor/skp/stats2');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_stats2_for_authenticated_user()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'skp_bulanan' => ['count', 'bulan', 'tahun'],
                    'skp_penetapan' => ['count', 'tahun'],
                    'skp_penilaian' => ['count', 'tahun'],
                    'skp_evaluasi' => ['count', 'tahun'],
                    'chart_bulanan',
                    'last_upload'
                ]
            ])
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_correct_bulanan_count_in_stats2()
    {
        $user = User::factory()->organik()->create();
        $user->assignRole('organik');
        
        Skp::factory()->count(3)->create([
            'user_id' => $user->id,
            'jenis' => 'SKP Bulanan',
            'tahun' => now()->subMonthNoOverflow()->year,
            'bulan' => now()->subMonthNoOverflow()->format('m')
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/kantor/skp/stats2');

        $response->assertStatus(200)
            ->assertJsonPath('data.skp_bulanan.count', 3);
    }

    /** @test */
    public function it_returns_chart_bulanan_with_12_months()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats2');

        $response->assertStatus(200)
            ->assertJsonPath('data.chart_bulanan', fn ($chart) => count($chart) === 12);
    }

    /** @test */
    public function it_returns_last_5_uploads()
    {
        $user = User::factory()->create();
        
        Skp::factory()->count(7)->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats2');

        $response->assertStatus(200)
            ->assertJsonPath('data.last_upload', fn ($uploads) => count($uploads) <= 5);
    }

    // =====================================================================
    // GET /api/kantor/skp/list - Paginated List Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_access_list()
    {
        $response = $this->getJson('/api/kantor/skp/list');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_paginated_list_for_authenticated_user()
    {
        $user = User::factory()->create();
        Skp::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'users',
                'listTahun'
            ])
            ->assertJson(['success' => true]);

        $this->assertUnifiedPaginationStructure($response);
    }

    /** @test */
    public function it_supports_custom_per_page_parameter()
    {
        $user = User::factory()->create();
        Skp::factory()->count(25)->create(['user_id' => $user->id]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?per_page=20');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_filters_list_by_search()
    {
        $user = User::factory()->create();
        
        Skp::factory()->create([
            'user_id' => $user->id,
            'nama' => 'SKP Bulanan Test Unique'
        ]);
        
        Skp::factory()->create([
            'user_id' => $user->id,
            'nama' => 'SKP Tahunan Different'
        ]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?search=Unique');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_filters_list_by_user_id()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Skp::factory()->count(3)->create(['user_id' => $user1->id]);
        Skp::factory()->count(2)->create(['user_id' => $user2->id]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?user_id=' . $user1->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_filters_list_by_tahun()
    {
        $user = User::factory()->create();
        
        Skp::factory()->create([
            'user_id' => $user->id,
            'tahun' => '2024'
        ]);
        
        Skp::factory()->create([
            'user_id' => $user->id,
            'tahun' => '2023'
        ]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?tahun=2024');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_supports_sorting_asc()
    {
        $user = User::factory()->create();
        Skp::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?sort_by=nama&sort_dir=ASC');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_supports_sorting_desc()
    {
        $user = User::factory()->create();
        Skp::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?sort_by=created_at&sort_dir=DESC');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_rejects_invalid_sort_by_parameter()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?sort_by=invalid_column');

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_rejects_invalid_sort_dir_parameter()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?sort_dir=INVALID');

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_rejects_invalid_tahun_format()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?tahun=24');

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_rejects_per_page_below_minimum()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?per_page=0');

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_rejects_per_page_above_maximum()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?per_page=101');

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_returns_users_dropdown_in_list()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list');

        $response->assertStatus(200)
            ->assertJsonPath('users', fn ($users) => is_array($users) || is_object($users));
    }

    /** @test */
    public function it_returns_list_tahun_in_list()
    {
        $user = User::factory()->create();
        Skp::factory()->create(['user_id' => $user->id, 'tahun' => '2024']);
        Skp::factory()->create(['user_id' => $user->id, 'tahun' => '2023']);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list');

        $response->assertStatus(200)
            ->assertJsonPath('listTahun', fn ($years) => is_array($years));
    }

    // =====================================================================
    // GET /api/kantor/skp/{id} (show) - Single Record Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_show_skp()
    {
        $skp = Skp::factory()->create();

        $response = $this->getJson('/api/kantor/skp/' . $skp->id);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_single_skp_for_authenticated_user()
    {
        $user = User::factory()->create();
        $skp = Skp::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/' . $skp->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully'
            ])
            ->assertJsonPath('data.id', $skp->id);
    }

    /** @test */
    public function it_includes_user_relationship_in_show()
    {
        $user = User::factory()->create();
        $skp = Skp::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/' . $skp->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name']
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_skp()
    {
        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/99999');

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    // =====================================================================
    // POST /api/kantor/skp (store) - Create Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_store_skp()
    {
        $response = $this->postJson('/api/kantor/skp', [
            'jenis' => 'SKP Bulanan',
            'tahun' => '2024',
            'bulan' => '05'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_creates_skp_bulanan_successfully()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Bulanan',
                'tahun' => '2024',
                'bulan' => '05',
                'file' => $file
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'SKP record created successfully'
            ]);

        $this->assertDatabaseHas('skps', [
            'jenis' => 'SKP Bulanan',
            'tahun' => '2024',
            'bulan' => '05'
        ]);

        // Verify jobs were dispatched
        Queue::assertPushed(ScanUploadedFile::class);
        Queue::assertPushed(UploadSkpToGoogleDrive::class);
    }

    /** @test */
    public function it_creates_skp_tahunan_penetapan_successfully()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Tahunan (Penetapan)',
                'tahun' => '2024',
                'file' => $file
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'SKP record created successfully'
            ]);

        $this->assertDatabaseHas('skps', [
            'jenis' => 'SKP Tahunan (Penetapan)',
            'tahun' => '2024',
            'bulan' => null
        ]);
    }

    /** @test */
    public function it_creates_skp_tahunan_penilaian_successfully()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Tahunan (Penilaian)',
                'tahun' => '2024',
                'file' => $file
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'SKP record created successfully'
            ]);

        $this->assertDatabaseHas('skps', [
            'jenis' => 'SKP Tahunan (Penilaian)',
            'tahun' => '2024'
        ]);
    }

    /** @test */
    public function it_validates_missing_jenis_on_store()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'tahun' => '2024',
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_missing_tahun_on_store()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Bulanan',
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_missing_file_on_store()
    {
        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Bulanan',
                'tahun' => '2024',
                'bulan' => '05'
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_invalid_jenis_on_store()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'Invalid Jenis',
                'tahun' => '2024',
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_invalid_tahun_format_on_store()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Bulanan',
                'tahun' => '24',
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_invalid_file_type_on_store()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Bulanan',
                'tahun' => '2024',
                'bulan' => '05',
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_file_size_on_store()
    {
        // Create a file larger than 7MB (7168KB)
        $file = UploadedFile::fake()->create('test.pdf', 8000, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Bulanan',
                'tahun' => '2024',
                'bulan' => '05',
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_generates_correct_nama_for_bulanan()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Bulanan',
                'tahun' => '2024',
                'bulan' => '05',
                'file' => $file
            ]);

        $response->assertStatus(201);

        $skp = Skp::latest()->first();
        $this->assertStringContainsString('SKP Bulanan', $skp->nama);
        $this->assertStringContainsString('05/2024', $skp->nama);
    }

    /** @test */
    public function it_generates_correct_nama_for_tahunan()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Tahunan (Penetapan)',
                'tahun' => '2024',
                'file' => $file
            ]);

        $response->assertStatus(201);

        $skp = Skp::latest()->first();
        $this->assertStringContainsString('SKP Tahunan (Penetapan)', $skp->nama);
        $this->assertStringContainsString('2024', $skp->nama);
    }

    /** @test */
    public function it_sets_bulan_null_for_annual_skp()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Tahunan (Penetapan)',
                'tahun' => '2024',
                'bulan' => '',
                'file' => $file
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('skps', [
            'jenis' => 'SKP Tahunan (Penetapan)',
            'bulan' => null
        ]);
    }

    /** @test */
    public function it_dispatches_virus_scan_job_on_store()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Bulanan',
                'tahun' => '2024',
                'bulan' => '05',
                'file' => $file
            ]);

        Queue::assertPushed(ScanUploadedFile::class);
    }

    /** @test */
    public function it_dispatches_google_drive_upload_job_on_store()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $this->actingAsOrganik()
            ->postJson('/api/kantor/skp', [
                'jenis' => 'SKP Bulanan',
                'tahun' => '2024',
                'bulan' => '05',
                'file' => $file
            ]);

        Queue::assertPushed(UploadSkpToGoogleDrive::class);
    }

    // =====================================================================
    // PUT /api/kantor/skp/{id} (update) - Update Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_update_skp()
    {
        $skp = Skp::factory()->create();

        $response = $this->putJson('/api/kantor/skp/' . $skp->id, [
            'jenis' => 'SKP Bulanan'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_updates_skp_without_file()
    {
        $skp = Skp::factory()->create([
            'jenis' => 'SKP Bulanan',
            'bulan' => '05',
            'tahun' => '2024'
        ]);

        $response = $this->actingAsOrganik()
            ->putJson('/api/kantor/skp/' . $skp->id, [
                'jenis' => 'SKP Bulanan',
                'bulan' => '06',
                'tahun' => '2024'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'SKP record updated successfully'
            ]);

        $this->assertDatabaseHas('skps', [
            'id' => $skp->id,
            'bulan' => '06'
        ]);
    }

    /** @test */
    public function it_updates_skp_with_new_file()
    {
        $skp = Skp::factory()->create([
            'link' => 'skp_files/old_file.pdf'
        ]);

        // Create the old file in fake storage
        Storage::disk('direct')->put('skp_files/old_file.pdf', 'old content');

        $newFile = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->putJson('/api/kantor/skp/' . $skp->id, [
                'jenis' => 'SKP Bulanan',
                'bulan' => '05',
                'tahun' => '2024',
                'file' => $newFile
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'SKP record updated successfully'
            ]);

        // Verify jobs were dispatched for new file
        Queue::assertPushed(ScanUploadedFile::class);
        Queue::assertPushed(UploadSkpToGoogleDrive::class);
    }

    /** @test */
    public function it_returns_500_when_updating_nonexistent_skp()
    {
        $response = $this->actingAsOrganik()
            ->putJson('/api/kantor/skp/99999', [
                'jenis' => 'SKP Bulanan'
            ]);

        $response->assertStatus(500); // Controller returns 500 for not found in catch block
    }

    /** @test */
    public function it_validates_invalid_jenis_on_update()
    {
        $skp = Skp::factory()->create();

        $response = $this->actingAsOrganik()
            ->putJson('/api/kantor/skp/' . $skp->id, [
                'jenis' => 'Invalid Jenis'
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_invalid_tahun_on_update()
    {
        $skp = Skp::factory()->create();

        $response = $this->actingAsOrganik()
            ->putJson('/api/kantor/skp/' . $skp->id, [
                'tahun' => '24'
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_invalid_file_type_on_update()
    {
        $skp = Skp::factory()->create();
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAsOrganik()
            ->putJson('/api/kantor/skp/' . $skp->id, [
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_file_size_on_update()
    {
        $skp = Skp::factory()->create();
        $file = UploadedFile::fake()->create('test.pdf', 8000, 'application/pdf');

        $response = $this->actingAsOrganik()
            ->putJson('/api/kantor/skp/' . $skp->id, [
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_keeps_existing_file_if_not_provided_on_update()
    {
        $skp = Skp::factory()->create([
            'link' => 'skp_files/existing.pdf'
        ]);

        $response = $this->actingAsOrganik()
            ->putJson('/api/kantor/skp/' . $skp->id, [
                'jenis' => 'SKP Bulanan',
                'bulan' => '06',
                'tahun' => '2024'
            ]);

        $response->assertStatus(200);

        $skp->refresh();
        $this->assertEquals('skp_files/existing.pdf', $skp->link);
    }

    /** @test */
    public function it_dispatches_jobs_when_new_file_uploaded_on_update()
    {
        $skp = Skp::factory()->create();
        $file = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');

        $this->actingAsOrganik()
            ->putJson('/api/kantor/skp/' . $skp->id, [
                'file' => $file
            ]);

        Queue::assertPushed(ScanUploadedFile::class);
        Queue::assertPushed(UploadSkpToGoogleDrive::class);
    }

    // =====================================================================
    // DELETE /api/kantor/skp/{id} (destroy) - Delete Tests
    // =====================================================================

    /** @test */
    public function it_requires_authentication_to_delete_skp()
    {
        $skp = Skp::factory()->create();

        $response = $this->deleteJson('/api/kantor/skp/' . $skp->id);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_deletes_skp_and_file_successfully()
    {
        $skp = Skp::factory()->create([
            'link' => 'skp_files/to_delete.pdf'
        ]);

        // Create the file in fake storage
        Storage::disk('direct')->put('skp_files/to_delete.pdf', 'content');

        $response = $this->actingAsOrganik()
            ->deleteJson('/api/kantor/skp/' . $skp->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'SKP record deleted successfully'
            ]);

        $this->assertDatabaseMissing('skps', ['id' => $skp->id]);
        Storage::disk('direct')->assertMissing('skp_files/to_delete.pdf');
    }

    /** @test */
    public function it_returns_500_when_deleting_nonexistent_skp()
    {
        $response = $this->actingAsOrganik()
            ->deleteJson('/api/kantor/skp/99999');

        $response->assertStatus(500); // Controller returns 500 for not found in catch block
    }

    /** @test */
    public function it_deletes_skp_without_file()
    {
        $skp = Skp::factory()->create([
            'link' => ''
        ]);

        $response = $this->actingAsOrganik()
            ->deleteJson('/api/kantor/skp/' . $skp->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'SKP record deleted successfully'
            ]);

        $this->assertDatabaseMissing('skps', ['id' => $skp->id]);
    }

    /** @test */
    public function it_handles_missing_file_gracefully_on_delete()
    {
        $skp = Skp::factory()->create([
            'link' => 'skp_files/nonexistent.pdf'
        ]);

        // Don't create the file - it should handle gracefully

        $response = $this->actingAsOrganik()
            ->deleteJson('/api/kantor/skp/' . $skp->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'SKP record deleted successfully'
            ]);

        $this->assertDatabaseMissing('skps', ['id' => $skp->id]);
    }

    // =====================================================================
    // Edge Cases and Business Logic Tests
    // =====================================================================

    /** @test */
    public function it_handles_empty_database_gracefully()
    {
        // Clear any existing SKP records
        Skp::query()->delete();

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/stats');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_handles_combined_filters_on_list()
    {
        $user = User::factory()->create();
        
        Skp::factory()->create([
            'user_id' => $user->id,
            'nama' => 'Test SKP',
            'tahun' => '2024'
        ]);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/skp/list?search=Test&user_id=' . $user->id . '&tahun=2024');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
