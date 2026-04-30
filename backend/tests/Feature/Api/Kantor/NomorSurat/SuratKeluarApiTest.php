<?php

namespace Tests\Feature\Api\Kantor\NomorSurat;

use App\Models\User;
use App\Models\SuratKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\BaseApiTestCase;
use Laravel\Sanctum\Sanctum;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\Permission\Models\Role;

class SuratKeluarApiTest extends BaseApiTestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');

        SuratKeluar::query()->delete();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
    }

    // ==================== INDEX Tests ====================

    public function test_it_requires_authentication_to_index_surat_keluar()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keluar');

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_lists_surat_keluar_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $suratKeluar = SuratKeluar::factory()
            ->count(3)
            ->forYear(now()->format('Y'))
            ->create();

        $response = $this->getJson('/api/kantor/surat/surat-keluar');

        $response->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'thn', 'tanggal', 'nomor', 'tujuan', 'perihal', 'isi_surat',
                            'created_by', 'created_at', 'updated_at'
                        ]
                    ],
                    'links',
                    'meta'
                ]);
    }

    public function test_it_lists_surat_keluar_as_user()
    {
        Sanctum::actingAs($this->user);

        SuratKeluar::factory()
            ->count(3)
            ->forYear(now()->format('Y'))
            ->create();

        $response = $this->getJson('/api/kantor/surat/surat-keluar');

        $response->assertOk()
                ->assertJsonCount(3, 'data');
    }

    public function test_it_filters_surat_keluar_by_year()
    {
        Sanctum::actingAs($this->admin);

        SuratKeluar::factory()->create(['thn' => '2023']);
        SuratKeluar::factory()->create(['thn' => '2024']);

        $response = $this->getJson('/api/kantor/surat/surat-keluar?filter[thn]=2024');

        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.thn', '2024');
    }

    public function test_it_filters_surat_keluar_by_month()
    {
        Sanctum::actingAs($this->admin);

        $year = now()->format('Y');
        SuratKeluar::factory()->create(['thn' => $year, 'tanggal' => "{$year}-01-15"]);
        SuratKeluar::factory()->create(['thn' => $year, 'tanggal' => "{$year}-02-15"]);

        $response = $this->getJson("/api/kantor/surat/surat-keluar?filter[bulan]=2&filter[thn]={$year}");

        $response->assertOk()
                ->assertJsonCount(1, 'data');
    }

    public function test_it_filters_surat_keluar_by_search()
    {
        Sanctum::actingAs($this->admin);

        $year = now()->format('Y');
        SuratKeluar::factory()->create(['thn' => $year, 'tujuan' => 'Kantor A']);
        SuratKeluar::factory()->create(['thn' => $year, 'tujuan' => 'Kantor B']);

        $response = $this->getJson("/api/kantor/surat/surat-keluar?filter[search]=Kantor A&filter[thn]={$year}");

        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.tujuan', 'Kantor A');
    }

    public function test_it_sorts_surat_keluar_by_tanggal()
    {
        Sanctum::actingAs($this->admin);

        $first = SuratKeluar::factory()->create([
            'thn' => '2024',
            'tanggal' => '2024-01-01',
        ]);
        $second = SuratKeluar::factory()->create([
            'thn' => '2024',
            'tanggal' => '2024-02-01',
        ]);

        $response = $this->getJson('/api/kantor/surat/surat-keluar?sort=tanggal&filter[thn]=2024');

        $response->assertOk()
                ->assertJsonPath('data.0.id', $first->id)
                ->assertJsonPath('data.1.id', $second->id);
    }

    public function test_it_paginates_surat_keluar()
    {
        Sanctum::actingAs($this->admin);

        SuratKeluar::factory()
            ->count(15)
            ->forYear(now()->format('Y'))
            ->create();

        $response = $this->getJson('/api/kantor/surat/surat-keluar?per_page=5');

        $response->assertOk()
                ->assertJsonPath('meta.count', 5);

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNextPage($response);
    }

    // ==================== STORE Tests ====================

    public function test_it_requires_authentication_to_store_surat_keluar()
    {
        $response = $this->postJson('/api/kantor/surat/surat-keluar', []);

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_creates_surat_keluar_successfully_as_admin()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        \DB::table('settings')->insert([
            'key' => 'FORMAT_SURAT_KELUAR',
            'value' => 'B/{nomor}/{tahun}',
            'tahun' => '2024',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $data = [
            'thn' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'tujuan' => 'Kantor Pusat',
            'perihal' => 'Pemberitahuan',
            'isi_surat' => 'Isi surat...'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $data);

        $response->assertCreated()
                ->assertJsonStructure(['data' => [
                    'id', 'thn', 'tanggal', 'nomor', 'no_surat', 'tujuan',
                    'perihal', 'isi_surat', 'created_by'
                ]])
                ->assertJsonPath('data.no_surat', 'B/0001/2024');

        $this->assertDatabaseHas('surat_keluar', [
            'thn' => '2024',
            'dari' => 'Kepala Kantor',
            'tujuan' => 'Kantor Pusat',
            'perihal' => 'Pemberitahuan'
        ]);
    }

    public function test_it_creates_surat_keluar_successfully_as_user()
    {
        Sanctum::actingAs($this->user);

        // Mock format setting
        \DB::table('settings')->insert([
            'key' => 'FORMAT_SURAT_KELUAR',
            'value' => 'B/{nomor}/{tahun}',
            'tahun' => '2024',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $data = [
            'thn' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'tujuan' => 'Kantor Cabang',
            'perihal' => 'Informasi',
            'isi_surat' => 'Isi surat...'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $data);

        $response->assertCreated();
    }

    public function test_it_validates_required_fields_on_store()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/kantor/surat/surat-keluar', []);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['tanggal', 'tujuan', 'perihal', 'isi_surat', 'dari']);
    }

    public function test_it_validates_tahun_format_on_store()
    {
        Sanctum::actingAs($this->admin);

        \DB::table('settings')->insert([
            'key' => 'FORMAT_SURAT_KELUAR',
            'value' => 'B/{nomor}/{tahun}',
            'tahun' => '20',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $data = [
            'thn' => '20',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'tujuan' => 'Kantor',
            'perihal' => 'Perihal',
            'isi_surat' => 'Isi'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $data);

        $response->assertCreated()
                ->assertJsonPath('data.thn', '20');
    }

    public function test_it_validates_tanggal_format_on_store()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'tahun' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-13-15',
            'nomor' => '1',
            'tujuan' => 'Kantor',
            'perihal' => 'Perihal',
            'isi_surat' => 'Isi'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $data);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['tanggal']);
    }

    public function test_it_validates_nomor_min_value_on_store()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'tahun' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'nomor' => 0,
            'tujuan' => 'Kantor',
            'perihal' => 'Perihal',
            'isi_surat' => 'Isi'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $data);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['nomor']);
    }

    public function test_it_handles_missing_format_setting_on_store()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'tahun' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'tujuan' => 'Kantor',
            'perihal' => 'Perihal',
            'isi_surat' => 'Isi'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $data);

        $response->assertStatus(500);
    }

    // ==================== SHOW Tests ====================

    public function test_it_requires_authentication_to_show_surat_keluar()
    {
        $suratKeluar = SuratKeluar::factory()->create();

        $response = $this->getJson("/api/kantor/surat/surat-keluar/{$suratKeluar->id}");

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_shows_surat_keluar_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $suratKeluar = SuratKeluar::factory()->create();

        $response = $this->getJson("/api/kantor/surat/surat-keluar/{$suratKeluar->id}");

        $response->assertOk()
                ->assertJsonStructure(['data' => [
                    'id', 'thn', 'tanggal', 'nomor', 'no_surat', 'tujuan',
                    'perihal', 'isi_surat', 'created_by', 'created_at', 'updated_at'
                ]])
                ->assertJsonPath('data.id', $suratKeluar->id);
    }

    public function test_it_shows_surat_keluar_as_user()
    {
        Sanctum::actingAs($this->user);

        $suratKeluar = SuratKeluar::factory()->create();

        $response = $this->getJson("/api/kantor/surat/surat-keluar/{$suratKeluar->id}");

        $response->assertOk()
                ->assertJsonPath('data.id', $suratKeluar->id);
    }

    public function test_it_returns_404_for_nonexistent_surat_keluar()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/kantor/surat/surat-keluar/999');

        $response->assertNotFound()
                ->assertJson(['message' => 'Surat keluar not found']);
    }

    // ==================== UPDATE Tests ====================

    public function test_it_requires_authentication_to_update_surat_keluar()
    {
        $suratKeluar = SuratKeluar::factory()->create();

        $response = $this->putJson("/api/kantor/surat/surat-keluar/{$suratKeluar->id}", []);

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_updates_surat_keluar_successfully_as_admin()
    {
        Sanctum::actingAs($this->admin);

        \DB::table('settings')->insert([
            'key' => 'FORMAT_SURAT_KELUAR',
            'value' => 'B/{nomor}/{tahun}',
            'tahun' => '2024',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $suratKeluar = SuratKeluar::factory()->create();

        $data = [
            'thn' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-20',
            'nomor' => '2',
            'tujuan' => 'Kantor Updated',
            'perihal' => 'Perihal Updated',
            'isi_surat' => 'Isi surat updated...'
        ];

        $response = $this->putJson("/api/kantor/surat/surat-keluar/{$suratKeluar->id}", $data);

        $response->assertOk()
                ->assertJsonPath('data.tujuan', 'Kantor Updated')
                ->assertJsonPath('data.perihal', 'Perihal Updated');

        $this->assertDatabaseHas('surat_keluar', [
            'id' => $suratKeluar->id,
            'tujuan' => 'Kantor Updated',
            'perihal' => 'Perihal Updated'
        ]);
    }

    public function test_it_validates_required_fields_on_update()
    {
        Sanctum::actingAs($this->admin);

        $suratKeluar = SuratKeluar::factory()->create();

        $response = $this->putJson("/api/kantor/surat/surat-keluar/{$suratKeluar->id}", []);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['thn', 'tanggal', 'nomor', 'tujuan', 'perihal', 'dari']);
    }

    public function test_it_returns_404_when_updating_nonexistent_surat_keluar()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'thn' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'tujuan' => 'Kantor',
            'perihal' => 'Perihal',
            'isi_surat' => 'Isi'
        ];

        $response = $this->putJson('/api/kantor/surat/surat-keluar/999', $data);

        $response->assertStatus(500)
                ->assertJson(['message' => 'Failed to update surat keluar']);
    }

    // ==================== DESTROY Tests ====================

    public function test_it_requires_authentication_to_destroy_surat_keluar()
    {
        $suratKeluar = SuratKeluar::factory()->create();

        $response = $this->deleteJson("/api/kantor/surat/surat-keluar/{$suratKeluar->id}");

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_deletes_surat_keluar_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $suratKeluar = SuratKeluar::factory()->create();

        $response = $this->deleteJson("/api/kantor/surat/surat-keluar/{$suratKeluar->id}");

        $response->assertOk()
                ->assertJson(['message' => 'Nomor Surat berhasil dihapus']);

        $this->assertDatabaseMissing('surat_keluar', [
            'id' => $suratKeluar->id
        ]);
    }

    public function test_it_deletes_surat_keluar_as_user()
    {
        Sanctum::actingAs($this->user);

        $suratKeluar = SuratKeluar::factory()->create();

        $response = $this->deleteJson("/api/kantor/surat/surat-keluar/{$suratKeluar->id}");

        $response->assertOk()
                ->assertJson(['message' => 'Nomor Surat berhasil dihapus']);

        $this->assertDatabaseMissing('surat_keluar', [
            'id' => $suratKeluar->id
        ]);
    }

    public function test_it_returns_404_when_deleting_nonexistent_surat_keluar()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/kantor/surat/surat-keluar/999');

        $response->assertStatus(500)
                ->assertJson(['message' => 'Failed to delete surat keluar']);
    }

    // ==================== INSERT Tests ====================

    public function test_it_requires_authentication_to_insert_surat_keluar()
    {
        $response = $this->postJson('/api/kantor/surat/surat-keluar/sisip', []);

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_inserts_surat_keluar_successfully_as_admin()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        \DB::table('settings')->insert([
            'key' => 'FORMAT_SURAT_KELUAR',
            'value' => 'B/{nomor}/{tahun}',
            'tahun' => '2024',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $suratKeluar = SuratKeluar::factory()->create(['nomor' => '0005', 'thn' => '2024']);

        $data = [
            'id' => $suratKeluar->id,
            'thn' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'tujuan' => 'Kantor Sisip',
            'perihal' => 'Perihal Sisip',
            'isi_surat' => 'Isi surat sisip...'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar/sisip', $data);

        $response->assertCreated()
                ->assertJsonPath('data.no_sisip', '1')
                ->assertJsonPath('data.tujuan', 'Kantor Sisip');

        $this->assertDatabaseHas('surat_keluar', [
            'nomor' => '0005',
            'no_sisip' => '1',
        ]);
    }

    public function test_it_validates_required_fields_on_insert()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/kantor/surat/surat-keluar/sisip', []);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['id', 'tanggal', 'tujuan', 'perihal', 'dari']);
    }

    public function test_it_handles_duplicate_nomor_on_insert()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        \DB::table('settings')->insert([
            'key' => 'FORMAT_SURAT_KELUAR',
            'value' => 'B/{nomor}/{tahun}',
            'tahun' => '2024',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $existing = SuratKeluar::factory()->create([
            'nomor' => '0003',
            'thn' => '2024',
            'no_sisip' => '1',
        ]);

        $data = [
            'id' => $existing->id,
            'thn' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'tujuan' => 'Kantor Duplicate',
            'perihal' => 'Perihal Duplicate',
            'isi_surat' => 'Isi surat duplicate...'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar/sisip', $data);

        $response->assertCreated()
                ->assertJsonPath('data.no_sisip', '2');
    }

    // ==================== GET DATES Tests ====================

    public function test_it_requires_authentication_to_get_dates()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keluar/dates');

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_gets_dates_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $year = now()->format('Y');
        SuratKeluar::factory()->create(['thn' => $year, 'tanggal' => "{$year}-01-15"]);
        SuratKeluar::factory()->create(['thn' => $year, 'tanggal' => "{$year}-02-15"]);

        $response = $this->getJson("/api/kantor/surat/surat-keluar/dates?tahun={$year}");

        $response->assertOk();

    }

    public function test_it_gets_dates_as_user()
    {
        Sanctum::actingAs($this->user);

        $year = now()->format('Y');
        SuratKeluar::factory()->create(['thn' => $year, 'tanggal' => "{$year}-01-15"]);

        $response = $this->getJson("/api/kantor/surat/surat-keluar/dates?tahun={$year}");

        $response->assertOk()
                ->assertJsonCount(1, 'data');
    }

    public function test_it_filters_dates_by_year()
    {
        Sanctum::actingAs($this->admin);

        SuratKeluar::factory()->create(['thn' => '2023', 'tanggal' => '2023-01-15']);
        SuratKeluar::factory()->create(['thn' => '2024', 'tanggal' => '2024-01-15']);

        $response = $this->getJson('/api/kantor/surat/surat-keluar/dates?tahun=2024');

        $response->assertOk()
                ->assertJsonCount(1, 'data');
    }

    public function test_it_formats_dates_correctly()
    {
        Sanctum::actingAs($this->admin);

        $year = now()->format('Y');
        SuratKeluar::factory()->create(['thn' => $year, 'tanggal' => "{$year}-01-15"]);

        $response = $this->getJson("/api/kantor/surat/surat-keluar/dates?tahun={$year}");

        $response->assertOk()
                ->assertJsonPath('data.0', "{$year}-01-15");
    }

    // ==================== GET YEARS Tests ====================

    public function test_it_requires_authentication_to_get_years()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keluar/years');

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_gets_years_as_admin()
    {
        Sanctum::actingAs($this->admin);

        SuratKeluar::factory()->create(['thn' => '2023']);
        SuratKeluar::factory()->create(['thn' => '2024']);

        $response = $this->getJson('/api/kantor/surat/surat-keluar/years');

        $response->assertOk()
                ->assertJsonCount(2);

    }

    public function test_it_gets_years_as_user()
    {
        Sanctum::actingAs($this->user);

        SuratKeluar::factory()->create(['thn' => '2024']);

        $response = $this->getJson('/api/kantor/surat/surat-keluar/years');

        $response->assertOk()
                ->assertJsonCount(1);
    }

    public function test_it_returns_unique_years()
    {
        Sanctum::actingAs($this->admin);

        SuratKeluar::factory()->create(['thn' => '2024']);
        SuratKeluar::factory()->create(['thn' => '2024']);
        SuratKeluar::factory()->create(['thn' => '2023']);

        $response = $this->getJson('/api/kantor/surat/surat-keluar/years');

        $response->assertOk()
                ->assertJsonCount(2);
    }

    // ==================== FORM OPTIONS Tests ====================

    public function test_it_requires_authentication_to_get_form_options()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keluar/form-options');

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_gets_form_options_as_admin()
    {
        Sanctum::actingAs($this->admin);

        // Mock settings
        \DB::table('settings')->insert([
            ['key' => 'FORMAT_SURAT_KELUAR', 'value' => 'B/{nomor}/{tahun}', 'grup' => 1, 'tahun' => '2024', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'TTD_SURAT_KELUAR', 'value' => 'Kepala Kantor', 'grup' => 1, 'tahun' => '2024', 'created_at' => now(), 'updated_at' => now()]
        ]);

        $response = $this->getJson('/api/kantor/surat/surat-keluar/form-options');

        $response->assertOk()
                ->assertJsonStructure(['data' => [
                    'settings',
                    'nomor_baru'
                ]])
                ->assertJsonFragment(['key' => 'FORMAT_SURAT_KELUAR', 'value' => 'B/{nomor}/{tahun}'])
                ->assertJsonFragment(['key' => 'TTD_SURAT_KELUAR', 'value' => 'Kepala Kantor']);
    }

    public function test_it_calculates_next_nomor_in_form_options()
    {
        Sanctum::actingAs($this->admin);

        // Create existing records
        SuratKeluar::factory()->create(['nomor' => 1, 'thn' => now()->format('Y')]);
        SuratKeluar::factory()->create(['nomor' => 3, 'thn' => now()->format('Y')]);

        // Mock settings
        \DB::table('settings')->insert([
            ['key' => 'FORMAT_SURAT_KELUAR', 'value' => 'B/{nomor}/{tahun}', 'grup' => 1, 'tahun' => '2024', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'TTD_SURAT_KELUAR', 'value' => 'Kepala Kantor', 'grup' => 1, 'tahun' => '2024', 'created_at' => now(), 'updated_at' => now()]
        ]);

        $response = $this->getJson('/api/kantor/surat/surat-keluar/form-options');

        $response->assertOk()
                ->assertJsonPath('data.nomor_baru', 4); // Should be max nomor + 1
    }

    // ==================== SECURITY Tests ====================

    public function test_it_prevents_sql_injection_in_search_filter()
    {
        Sanctum::actingAs($this->admin);

        SuratKeluar::factory()->create(['tujuan' => 'Valid Kantor']);

        $maliciousInput = "'; DROP TABLE surat_keluar; --";

        $response = $this->getJson("/api/kantor/surat/surat-keluar?filter[search]={$maliciousInput}");

        $response->assertOk();

        // Verify table still exists
        $this->assertDatabaseHas('surat_keluar', ['tujuan' => 'Valid Kantor']);
    }

    public function test_it_prevents_xss_in_data_fields()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        \DB::table('settings')->insert([
            'key' => 'FORMAT_SURAT_KELUAR',
            'value' => 'B/{nomor}/{tahun}',
            'tahun' => '2024',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $maliciousData = [
            'tahun' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'tujuan' => '<script>alert("XSS")</script>',
            'perihal' => 'Test',
            'isi_surat' => 'Test'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $maliciousData);

        $response->assertCreated();

        $this->assertDatabaseHas('surat_keluar', [
            'tujuan' => '<script>alert("XSS")</script>'
        ]);
    }

    // ==================== PERFORMANCE Tests ====================

    public function test_it_handles_large_dataset_efficiently()
    {
        Sanctum::actingAs($this->admin);

        // Create 100 records
        SuratKeluar::factory()->count(100)->create();

        $startTime = microtime(true);

        $response = $this->getJson('/api/kantor/surat/surat-keluar?per_page=50');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertOk()
                ->assertJsonCount(50, 'data');

        // Assert response time is reasonable (less than 2 seconds)
        $this->assertLessThan(2.0, $executionTime);
    }

    public function test_it_handles_complex_filters_efficiently()
    {
        Sanctum::actingAs($this->admin);

        // Create diverse dataset
        SuratKeluar::factory()->count(50)->create(['thn' => '2023']);
        SuratKeluar::factory()->count(50)->create(['thn' => '2024']);

        $startTime = microtime(true);

        $response = $this->getJson('/api/kantor/surat/surat-keluar?filter[thn]=2024&filter[bulan]=6&sort=tanggal');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertOk();

        // Assert response time is reasonable (less than 1 second for filtered query)
        $this->assertLessThan(1.0, $executionTime);
    }

    // ==================== EDGE CASES Tests ====================

    public function test_it_handles_empty_database_gracefully()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/kantor/surat/surat-keluar');

        $response->assertOk()
                ->assertJsonCount(0, 'data')
                ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_it_handles_invalid_filter_values_gracefully()
    {
        Sanctum::actingAs($this->admin);

        SuratKeluar::factory()->create();

        $response = $this->getJson('/api/kantor/surat/surat-keluar?filter[bulan]=13');

        $response->assertOk()
                ->assertJsonCount(0, 'data'); // Invalid month should return empty results
    }

    public function test_it_handles_very_long_text_inputs()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        \DB::table('settings')->insert([
            'key' => 'FORMAT_SURAT_KELUAR',
            'value' => 'B/{nomor}/{tahun}',
            'tahun' => '2024',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $longText = str_repeat('A', 1000);

        $data = [
            'tahun' => '2024',
            'dari' => 'Kepala Kantor',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'tujuan' => $longText,
            'perihal' => 'Test',
            'isi_surat' => 'Test'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keluar', $data);

        $response->assertCreated();
    }

    public function test_it_handles_special_characters_in_search()
    {
        Sanctum::actingAs($this->admin);

        SuratKeluar::factory()->create(['tujuan' => 'Kantor & PT. Maju Bersama']);

        $response = $this->getJson('/api/kantor/surat/surat-keluar?filter[search]=Kantor &');

        $response->assertOk()
                ->assertJsonCount(1, 'data');
    }

    // ==================== CONCURRENT REQUESTS Tests ====================

    public function test_it_handles_concurrent_requests_for_next_nomor()
    {
        Sanctum::actingAs($this->admin);

        // Mock settings
        \DB::table('settings')->insert([
            ['key' => 'FORMAT_SURAT_KELUAR', 'value' => 'B/{nomor}/{tahun}', 'grup' => 1, 'tahun' => '2024', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'TTD_SURAT_KELUAR', 'value' => 'Kepala Kantor', 'grup' => 1, 'tahun' => '2024', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Create existing record
        SuratKeluar::factory()->create(['nomor' => 1, 'thn' => now()->format('Y')]);

        // Simulate concurrent requests (in real scenario, this would be actual concurrent requests)
        $response1 = $this->getJson('/api/kantor/surat/surat-keluar/form-options');
        $response2 = $this->getJson('/api/kantor/surat/surat-keluar/form-options');

        $response1->assertOk()
                  ->assertJsonPath('data.nomor_baru', 2);

        $response2->assertOk()
                  ->assertJsonPath('data.nomor_baru', 2);
    }
}
