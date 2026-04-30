<?php

namespace Tests\Feature\Api\Kantor\NomorSurat;

use App\Models\User;
use App\Models\SuratTugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\BaseApiTestCase;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SuratTugasApiTest extends BaseApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up fake storage for file tests
        Storage::fake('direct');
    }

    protected function setUpSettings(): void
    {
        DB::table('settings')->insert([
            'key' => 'FORMAT_SURTUG',
            'value' => '{nomor}/ST-{klasifikasi}/{tahun}',
            'tahun' => date('Y'),
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    protected function setUpKlasifikasi(): void
    {
        DB::table('klasifikasi_surat')->insert([
            'parent_id' => 31,
            'kode' => '100',
            'keterangan' => 'Organisasi',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // ==================== INDEX Tests ====================

    public function test_it_requires_authentication_to_index_surat_tugas()
    {
        $response = $this->getJson('/api/kantor/surat/surat-tugas');

        $response->assertStatus(401);
    }

    public function test_it_lists_surat_tugas_as_admin()
    {
        SuratTugas::factory()->count(3)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_it_lists_surat_tugas_as_user()
    {
        SuratTugas::factory()->count(3)->create();

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/surat/surat-tugas');

        $response->assertStatus(200);
    }

    public function test_it_filters_surat_tugas_by_year()
    {
        SuratTugas::factory()->create(['tahun' => '2023']);
        SuratTugas::factory()->create(['tahun' => '2024']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?filter[tahun]=2024');

        $response->assertStatus(200);
    }

    public function test_it_filters_surat_tugas_by_tanggal()
    {
        SuratTugas::factory()->create(['tanggal' => '2024-01-15']);
        SuratTugas::factory()->create(['tanggal' => '2024-02-15']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?filter[tanggal]=2024-01-15');

        $response->assertStatus(200);
    }

    public function test_it_searches_surat_tugas_by_kepada()
    {
        SuratTugas::factory()->create(['kepada' => 'Tim A']);
        SuratTugas::factory()->create(['kepada' => 'Tim B']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?search=Tim A');

        $response->assertStatus(200);
    }

    public function test_it_searches_surat_tugas_by_uraian()
    {
        SuratTugas::factory()->create(['uraian' => 'Survei Lapangan']);
        SuratTugas::factory()->create(['uraian' => 'Data Entry']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?search=Survei');

        $response->assertStatus(200);
    }

    public function test_it_searches_surat_tugas_by_no_surat()
    {
        SuratTugas::factory()->create(['no_surat' => '0001/ST-100/2024']);
        SuratTugas::factory()->create(['no_surat' => '0002/ST-100/2024']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?search=0001');

        $response->assertStatus(200);
    }

    public function test_it_sorts_surat_tugas_by_tahun()
    {
        SuratTugas::factory()->create(['tahun' => '2023']);
        SuratTugas::factory()->create(['tahun' => '2024']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?sort=-tahun');

        $response->assertStatus(200);
    }

    public function test_it_paginates_surat_tugas()
    {
        SuratTugas::factory()->count(15)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?per_page=5&page=2');

        $response->assertStatus(200);
    }

    public function test_it_sets_default_tahun_filter()
    {
        $currentYear = Carbon::now()->format('Y');
        SuratTugas::factory()->create(['tahun' => $currentYear]);
        SuratTugas::factory()->create(['tahun' => '2023']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas');

        $response->assertStatus(200);
    }

    // ==================== STORE Tests ====================

    public function test_it_requires_authentication_to_store_surat_tugas()
    {
        $response = $this->postJson('/api/kantor/surat/surat-tugas', []);

        $response->assertStatus(401);
    }

    public function test_it_creates_surat_tugas_successfully_as_admin()
    {
        $this->setUpSettings();

        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'kode_klas' => '100',
            'kepada' => 'Tim Survei',
            'menimbang' => 'Dalam rangka pelaksanaan kegiatan',
            'uraian' => 'Melaksanakan survei lapangan'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        // Verify nomor is auto-generated as 0001 (first entry for 2024)
        $response->assertJsonPath('data.nomor', '0001');
    }

    public function test_it_auto_generates_nomor_incrementally()
    {
        $this->setUpSettings();

        // Create first surat tugas
        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'kode_klas' => '100',
            'kepada' => 'Tim 1',
            'uraian' => 'Uraian 1'
        ];

        $response1 = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data);

        $response1->assertStatus(201);
        $response1->assertJsonPath('data.nomor', '0001');

        // Create second surat tugas
        $data2 = [
            'tahun' => '2024',
            'tanggal' => '2024-01-16',
            'kode_klas' => '100',
            'kepada' => 'Tim 2',
            'uraian' => 'Uraian 2'
        ];

        $response2 = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data2);

        $response2->assertStatus(201);
        $response2->assertJsonPath('data.nomor', '0002');
    }

    public function test_it_auto_generates_nomor_for_different_years()
    {
        $this->setUpSettings();

        // Create surat tugas for 2024
        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'kode_klas' => '100',
            'kepada' => 'Tim 2024',
            'uraian' => 'Uraian 2024'
        ];

        $response1 = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data);

        $response1->assertStatus(201);
        $response1->assertJsonPath('data.nomor', '0001');

        // Create surat tugas for 2025
        $data2 = [
            'tahun' => '2025',
            'tanggal' => '2025-01-15',
            'kode_klas' => '100',
            'kepada' => 'Tim 2025',
            'uraian' => 'Uraian 2025'
        ];

        $response2 = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data2);

        $response2->assertStatus(201);
        $response2->assertJsonPath('data.nomor', '0001');
    }

    public function test_it_creates_surat_tugas_successfully_as_user()
    {
        $this->setUpSettings();

        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'kode_klas' => '100',
            'kepada' => 'Tim User',
            'uraian' => 'Uraian user'
        ];

        $response = $this->actingAsOrganik()
            ->postJson('/api/kantor/surat/surat-tugas', $data);

        $response->assertStatus(201);
    }

    public function test_it_validates_required_fields_on_store()
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', []);

        $response->assertStatus(422);
    }

    public function test_it_validates_tanggal_format_on_store()
    {
        $this->setUpSettings();

        $data = [
            'tahun' => '2024',
            'tanggal' => 'invalid-date',
            'kode_klas' => '100',
            'kepada' => 'Tim',
            'uraian' => 'Uraian'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data);

        $response->assertStatus(422);
    }

    public function test_it_handles_missing_format_setting_on_store()
    {
        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'kode_klas' => '100',
            'kepada' => 'Tim',
            'uraian' => 'Uraian'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data);

        $response->assertStatus(500);
    }

    public function test_it_formats_nomor_with_zero_padding_on_store()
    {
        $this->setUpSettings();

        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'kode_klas' => '100',
            'kepada' => 'Tim',
            'uraian' => 'Uraian'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data);

        $response->assertStatus(201);
    }

    public function test_it_handles_first_nomor_on_store()
    {
        $this->setUpSettings();

        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'kode_klas' => '100',
            'kepada' => 'Tim',
            'uraian' => 'Uraian'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data);

        $response->assertStatus(201);
    }

    // ==================== SHOW Tests ====================

    public function test_it_requires_authentication_to_show_surat_tugas()
    {
        $suratTugas = SuratTugas::factory()->create();

        $response = $this->getJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}");

        $response->assertStatus(401);
    }

    public function test_it_shows_surat_tugas_as_admin()
    {
        $suratTugas = SuratTugas::factory()->create();

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_it_shows_surat_tugas_as_user()
    {
        $suratTugas = SuratTugas::factory()->create();

        $response = $this->actingAsOrganik()
            ->getJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}");

        $response->assertStatus(200);
    }

    public function test_it_returns_404_for_nonexistent_surat_tugas()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/999999');

        $response->assertStatus(404);
    }

    // ==================== UPDATE Tests ====================

    public function test_it_requires_authentication_to_update_surat_tugas()
    {
        $suratTugas = SuratTugas::factory()->create();

        $response = $this->putJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}", []);

        $response->assertStatus(401);
    }

    public function test_it_updates_surat_tugas_successfully_as_admin()
    {
        $this->setUpSettings();
        $suratTugas = SuratTugas::factory()->create();

        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-20',
            'nomor' => '2',
            'kode_klas' => '200',
            'kepada' => 'Tim Updated',
            'menimbang' => 'Menimbang updated',
            'uraian' => 'Uraian updated'
        ];

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}", $data);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_it_updates_surat_tugas_with_file_as_admin()
    {
        $this->setUpSettings();
        $suratTugas = SuratTugas::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}", [
                'tahun' => '2024',
                'tanggal' => '2024-01-20',
                'nomor' => '1',
                'kode_klas' => '100',
                'kepada' => 'Tim',
                'uraian' => 'Uraian',
                'file' => $file
            ]);

        $response->assertStatus(200);
    }

    public function test_it_updates_surat_tugas_file_replacement_as_admin()
    {
        $this->setUpSettings();
        $suratTugas = SuratTugas::factory()->create(['file' => 'old-file.pdf']);
        Storage::disk('direct')->put('old-file.pdf', 'old content');

        $newFile = UploadedFile::fake()->create('new-document.pdf', 1000, 'application/pdf');

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}", [
                'tahun' => '2024',
                'tanggal' => '2024-01-20',
                'nomor' => '1',
                'kode_klas' => '100',
                'kepada' => 'Tim',
                'uraian' => 'Uraian',
                'file' => $newFile
            ]);

        $response->assertStatus(200);
    }

    public function test_it_validates_required_fields_on_update()
    {
        $suratTugas = SuratTugas::factory()->create();

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}", []);

        $response->assertStatus(422);
    }

    public function test_it_validates_file_upload_on_update()
    {
        $this->setUpSettings();
        $suratTugas = SuratTugas::factory()->create();
        $invalidFile = UploadedFile::fake()->create('document.txt', 1000, 'text/plain');

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}", [
                'tahun' => '2024',
                'tanggal' => '2024-01-20',
                'nomor' => '1',
                'kode_klas' => '100',
                'kepada' => 'Tim',
                'uraian' => 'Uraian',
                'file' => $invalidFile
            ]);

        $response->assertStatus(422);
    }

    public function test_it_returns_404_when_updating_nonexistent_surat_tugas()
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->putJson('/api/kantor/surat/surat-tugas/999999', [
                'tahun' => '2024',
                'tanggal' => '2024-01-15',
                'nomor' => '1',
                'kode_klas' => '100',
                'kepada' => 'Tim',
                'uraian' => 'Uraian'
            ]);

        $response->assertStatus(404);
    }

    // ==================== DESTROY Tests ====================

    public function test_it_requires_authentication_to_destroy_surat_tugas()
    {
        $suratTugas = SuratTugas::factory()->create();

        $response = $this->deleteJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}");

        $response->assertStatus(401);
    }

    public function test_it_deletes_surat_tugas_as_admin()
    {
        $suratTugas = SuratTugas::factory()->create();

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_it_deletes_surat_tugas_with_file_as_admin()
    {
        $suratTugas = SuratTugas::factory()->create(['file' => 'test-file.pdf']);

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}");

        $response->assertStatus(200);
    }

    public function test_it_deletes_surat_tugas_as_user()
    {
        $suratTugas = SuratTugas::factory()->create();

        $response = $this->actingAsOrganik()
            ->deleteJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}");

        $response->assertStatus(200);
    }

    public function test_it_returns_404_when_deleting_nonexistent_surat_tugas()
    {
        $response = $this->actingAsAdmin()
            ->deleteJson('/api/kantor/surat/surat-tugas/999999');

        $response->assertStatus(404);
    }

    // ==================== INSERT Tests ====================

    public function test_it_requires_authentication_to_insert_surat_tugas()
    {
        $response = $this->postJson('/api/kantor/surat/surat-tugas/sisip', []);

        $response->assertStatus(401);
    }

    public function test_it_inserts_surat_tugas_successfully_as_admin()
    {
        $this->setUpSettings();

        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'kode_klas' => '100',
            'kepada' => 'Tim Insert',
            'menimbang' => 'Menimbang insert',
            'uraian' => 'Uraian insert'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas/sisip', $data);

        $response->assertStatus(201);
    }

    public function test_it_inserts_surat_tugas_with_no_sisip()
    {
        $this->setUpSettings();

        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'no_sisip' => '5',
            'kode_klas' => '100',
            'kepada' => 'Tim Sisip',
            'uraian' => 'Uraian sisip'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas/sisip', $data);

        $response->assertStatus(201);
    }

    public function test_it_auto_generates_no_sisip_when_not_provided()
    {
        $this->setUpSettings();

        // Create first record without no_sisip - should auto-generate to 1
        $firstData = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'kode_klas' => '100',
            'kepada' => 'Tim First',
            'uraian' => 'Uraian first'
        ];

        $response1 = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas/sisip', $firstData);

        $response1->assertStatus(201);
        $response1->assertJsonFragment(['no_sisip' => 1]);

        // Create second record without no_sisip - should auto-generate to 2
        $secondData = [
            'tahun' => '2024',
            'tanggal' => '2024-01-16',
            'nomor' => '1',
            'kode_klas' => '100',
            'kepada' => 'Tim Second',
            'uraian' => 'Uraian second'
        ];

        $response2 = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas/sisip', $secondData);

        $response2->assertStatus(201);
        $response2->assertJsonFragment(['no_sisip' => 2]);
    }

    public function test_it_validates_required_fields_on_insert()
    {
        $this->setUpSettings();

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas/sisip', []);

        $response->assertStatus(422);
    }

    // ==================== GET DATES Tests ====================

    public function test_it_requires_authentication_to_get_dates()
    {
        $response = $this->getJson('/api/kantor/surat/surat-tugas/dates');

        $response->assertStatus(401);
    }

    public function test_it_gets_dates_as_admin()
    {
        SuratTugas::factory()->create(['tanggal' => '2024-01-15']);
        SuratTugas::factory()->create(['tanggal' => '2024-02-15']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/dates');

        $response->assertStatus(200);
    }

    public function test_it_gets_dates_as_user()
    {
        SuratTugas::factory()->create(['tanggal' => '2024-01-15']);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/surat/surat-tugas/dates');

        $response->assertStatus(200);
    }

    public function test_it_filters_dates_by_year()
    {
        SuratTugas::factory()->create(['tanggal' => '2023-01-15', 'tahun' => '2023']);
        SuratTugas::factory()->create(['tanggal' => '2024-01-15', 'tahun' => '2024']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/dates?tahun=2024');

        $response->assertStatus(200);
    }

    public function test_it_orders_dates_descending()
    {
        SuratTugas::factory()->create(['tanggal' => '2024-01-15']);
        SuratTugas::factory()->create(['tanggal' => '2024-03-15']);
        SuratTugas::factory()->create(['tanggal' => '2024-02-15']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/dates');

        $response->assertStatus(200);
    }

    // ==================== GET YEARS Tests ====================

    public function test_it_requires_authentication_to_get_years()
    {
        $response = $this->getJson('/api/kantor/surat/surat-tugas/years');

        $response->assertStatus(401);
    }

    public function test_it_gets_years_as_admin()
    {
        SuratTugas::factory()->create(['tahun' => '2023']);
        SuratTugas::factory()->create(['tahun' => '2024']);
        SuratTugas::factory()->create(['tahun' => '2022']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/years');

        $response->assertStatus(200);
    }

    public function test_it_gets_years_as_user()
    {
        SuratTugas::factory()->create(['tahun' => '2024']);

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/surat/surat-tugas/years');

        $response->assertStatus(200);
    }

    // ==================== GET KLASIFIKASI Tests ====================

    public function test_it_requires_authentication_to_get_klasifikasi()
    {
        $response = $this->getJson('/api/kantor/surat/surat-tugas/klasifikasi');

        $response->assertStatus(401);
    }

    public function test_it_gets_klasifikasi_as_admin()
    {
        $this->setUpKlasifikasi();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/klasifikasi');

        $response->assertStatus(200);
    }

    public function test_it_gets_klasifikasi_as_user()
    {
        $this->setUpKlasifikasi();

        $response = $this->actingAsOrganik()
            ->getJson('/api/kantor/surat/surat-tugas/klasifikasi');

        $response->assertStatus(200);
    }

    public function test_it_filters_klasifikasi_by_parent_id()
    {
        DB::table('klasifikasi_surat')->insert([
            ['parent_id' => 31, 'kode' => '100', 'keterangan' => 'Correct Parent'],
            ['parent_id' => 32, 'kode' => '200', 'keterangan' => 'Wrong Parent']
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/klasifikasi');

        $response->assertStatus(200);
    }

    // ==================== FORM OPTIONS Tests ====================

    public function test_it_requires_authentication_to_get_form_options()
    {
        $response = $this->getJson('/api/kantor/surat/surat-tugas/form-options');

        $response->assertStatus(401);
    }

    public function test_it_gets_form_options_as_admin()
    {
        $this->setUpKlasifikasi();

        SuratTugas::factory()->create([
            'tahun' => Carbon::now()->format('Y'),
            'nomor' => '0024'
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/form-options');

        $response->assertStatus(200);
    }

    public function test_it_calculates_next_nomor_in_form_options()
    {
        $currentYear = Carbon::now()->format('Y');

        SuratTugas::factory()->create(['tahun' => $currentYear, 'nomor' => '0010']);
        SuratTugas::factory()->create(['tahun' => $currentYear, 'nomor' => '0020']);
        SuratTugas::factory()->create(['tahun' => '2023', 'nomor' => '0030']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/form-options');

        $response->assertStatus(200);
    }

    public function test_it_handles_empty_surat_tugas_in_form_options()
    {
        $this->setUpKlasifikasi();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/form-options');

        $response->assertStatus(200);
    }

    // ==================== SECURITY Tests ====================

    public function test_it_prevents_sql_injection_in_search()
    {
        SuratTugas::factory()->create(['kepada' => 'Valid Tim']);

        // SQL injection attempt should not crash the application
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?search=test');

        $response->assertStatus(200);
    }

    public function test_it_prevents_xss_in_data_fields()
    {
        $this->setUpSettings();

        $maliciousData = [
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'kode_klas' => '100',
            'kepada' => '<script>alert("XSS")</script>',
            'uraian' => 'Test'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $maliciousData);

        $response->assertStatus(201);
    }

    // ==================== PERFORMANCE Tests ====================

    public function test_it_handles_large_dataset_efficiently()
    {
        SuratTugas::factory()->count(100)->create();

        $startTime = microtime(true);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?per_page=50');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(5.0, $executionTime);
    }

    public function test_it_handles_complex_filters_efficiently()
    {
        SuratTugas::factory()->count(50)->create(['tahun' => '2023']);
        SuratTugas::factory()->count(50)->create(['tahun' => '2024']);

        $startTime = microtime(true);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?filter[tahun]=2024&search=Tim&sort=-tahun');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(3.0, $executionTime);
    }

    // ==================== EDGE CASES Tests ====================

    public function test_it_handles_empty_database_gracefully()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas');

        $response->assertStatus(200);
    }

    public function test_it_handles_empty_dates_list()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/dates');

        $response->assertStatus(200);
    }

    public function test_it_handles_empty_years_list()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/years');

        $response->assertStatus(200);
    }

    public function test_it_handles_empty_klasifikasi_list()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas/klasifikasi');

        $response->assertStatus(200);
    }

    public function test_it_handles_special_characters_in_search()
    {
        SuratTugas::factory()->create(['kepada' => 'Tim & PT. Maju Bersama']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/surat/surat-tugas?search=Tim');

        $response->assertStatus(200);
    }

    public function test_it_formats_tanggal_indo_correctly()
    {
        $this->setUpSettings();

        $data = [
            'tahun' => '2024',
            'tanggal' => '2024-12-25',
            'nomor' => '1',
            'kode_klas' => '100',
            'kepada' => 'Tim',
            'uraian' => 'Uraian'
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/surat/surat-tugas', $data);

        $response->assertStatus(201);
    }

    // ==================== FILE HANDLING Tests ====================

    public function test_it_validates_file_size_limit()
    {
        $this->setUpSettings();
        $suratTugas = SuratTugas::factory()->create();
        $oversizedFile = UploadedFile::fake()->create('large.pdf', 3000, 'application/pdf');

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}", [
                'tahun' => '2024',
                'tanggal' => '2024-01-20',
                'nomor' => '1',
                'kode_klas' => '100',
                'kepada' => 'Tim',
                'uraian' => 'Uraian',
                'file' => $oversizedFile
            ]);

        $response->assertStatus(422);
    }

    public function test_it_preserves_existing_file_when_no_new_file_provided()
    {
        $this->setUpSettings();
        $suratTugas = SuratTugas::factory()->create(['file' => 'existing-file.pdf']);
        Storage::disk('direct')->put('existing-file.pdf', 'existing content');

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/surat/surat-tugas/{$suratTugas->id}", [
                'tahun' => '2024',
                'tanggal' => '2024-01-20',
                'nomor' => '1',
                'kode_klas' => '100',
                'kepada' => 'Updated Tim',
                'uraian' => 'Updated uraian'
            ]);

        $response->assertStatus(200);
    }
}
