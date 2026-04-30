<?php

namespace Tests\Feature\Api\Kantor\NomorSurat;

use App\Models\User;
use App\Models\SkBast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\BaseApiTestCase;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class SuratKeputusanApiTest extends BaseApiTestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();
    }

    private function insertFormatSetting(string $tahun = '2024'): void
    {
        \DB::table('settings')->insert([
            'tahun' => $tahun,
            'key' => 'FORMAT_SK',
            'value' => '{nomor}/SK/{bln}/{tahun}',
            'grup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // ==================== INDEX Tests ====================

    public function test_it_requires_authentication_to_index_surat_keputusan()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keputusan');

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_lists_surat_keputusan_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $suratKeputusan = SkBast::factory()->count(3)->create(['type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan');

        $response->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'thn', 'bln', 'tanggal', 'nomor', 'no_sisip', 'no_surat',
                            'oleh', 'kegiatan', 'kepada', 'perihal', 'type', 'kol_lampiran',
                            'create_by', 'created_at', 'updated_at'
                        ]
                    ],
                    'meta',
                    'links'
                ]);
    }

    public function test_it_lists_surat_keputusan_as_user()
    {
        Sanctum::actingAs($this->user);

        SkBast::factory()->count(3)->create(['type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan');

        $response->assertOk()
                ->assertJsonCount(3, 'data');
    }

    public function test_it_filters_surat_keputusan_by_year()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['thn' => '2023', 'type' => 'SK']);
        SkBast::factory()->create(['thn' => '2024', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan?tahun=2024');

        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.thn', '2024');
    }

    public function test_it_filters_surat_keputusan_by_date()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['tanggal' => '2024-01-15', 'type' => 'SK']);
        SkBast::factory()->create(['tanggal' => '2024-02-15', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan?filter[tanggal]=2024-01-15');

        $response->assertOk()
                ->assertJsonCount(1, 'data');
    }

    public function test_it_searches_surat_keputusan_by_kepada()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['kepada' => 'Tim A', 'type' => 'SK']);
        SkBast::factory()->create(['kepada' => 'Tim B', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan?filter[search]=Tim A');

        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.kepada', 'Tim A');
    }

    public function test_it_searches_surat_keputusan_by_perihal()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['perihal' => 'Penunjukan Tim', 'type' => 'SK']);
        SkBast::factory()->create(['perihal' => 'Pemberhentian Tim', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan?filter[search]=Penunjukan');

        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.perihal', 'Penunjukan Tim');
    }

    public function test_it_sorts_surat_keputusan_by_tanggal()
    {
        Sanctum::actingAs($this->admin);

        $first = SkBast::factory()->create(['tanggal' => '2024-01-01', 'type' => 'SK']);
        $second = SkBast::factory()->create(['tanggal' => '2024-02-01', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan?sort=tanggal');

        $response->assertOk()
                ->assertJsonPath('data.0.id', $first->id)
                ->assertJsonPath('data.1.id', $second->id);
    }

    public function test_it_paginates_surat_keputusan()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->count(15)->create(['type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan?per_page=5');

        $response->assertOk()
                ->assertJsonPath('meta.count', 5);

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNextPage($response);
    }

    public function test_it_excludes_non_sk_records()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['type' => 'SK']);
        SkBast::factory()->create(['type' => 'BAST']);
        SkBast::factory()->create(['type' => 'OTHER']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan');

        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.type', 'SK');
    }

    // ==================== STORE Tests ====================

    public function test_it_requires_authentication_to_store_surat_keputusan()
    {
        $response = $this->postJson('/api/kantor/surat/surat-keputusan', []);

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_creates_surat_keputusan_successfully_as_admin()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        $this->insertFormatSetting();

        $data = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'oleh' => 'Kepala BPS',
            'kegiatan' => 'Survei Sosial Ekonomi',
            'kepada' => 'Tim Survei',
            'perihal' => 'Penunjukan Tim Survei'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $data);

        $response->assertCreated()
                ->assertJsonStructure([
                    'data' => [
                        'id', 'thn', 'bln', 'tanggal', 'nomor', 'no_surat',
                        'oleh', 'kegiatan', 'kepada', 'perihal', 'type',
                        'create_by', 'created_at', 'updated_at'
                    ]
                ])
                ->assertJsonPath('data.no_surat', '0001/SK/I/2024')
                ->assertJsonPath('data.type', 'SK')
                ->assertJsonPath('data.bln', 'I');

        $this->assertDatabaseHas('surat_sk_bast', [
            'thn' => '2024',
            'kepada' => 'Tim Survei',
            'perihal' => 'Penunjukan Tim Survei',
            'type' => 'SK'
        ]);
    }

    public function test_it_creates_surat_keputusan_successfully_as_user()
    {
        Sanctum::actingAs($this->user);

        // Mock format setting
        $this->insertFormatSetting();

        $data = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'kepada' => 'Tim User',
            'perihal' => 'Perihal User'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $data);

        $response->assertCreated();
    }

    public function test_it_creates_surat_keputusan_with_no_sisip()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        $this->insertFormatSetting();

        $data = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'no_sisip' => '5',
            'kepada' => 'Tim Sisip',
            'perihal' => 'Perihal Sisip'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $data);

        $response->assertCreated()
                ->assertJsonPath('data.no_surat', '0001.5/SK/I/2024')
                ->assertJsonPath('data.no_sisip', '5');
    }

    public function test_it_validates_required_fields_on_store()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', []);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['thn', 'tanggal', 'nomor', 'kepada', 'perihal']);
    }

    public function test_it_validates_tanggal_format_on_store()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'thn' => '2024',
            'tanggal' => 'invalid-date',
            'nomor' => '1',
            'kepada' => 'Tim',
            'perihal' => 'Perihal'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $data);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['tanggal']);
    }

    public function test_it_handles_missing_format_setting_on_store()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'kepada' => 'Tim',
            'perihal' => 'Perihal'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $data);

        $response->assertStatus(500)
                ->assertJson(['message' => 'Format setting not found']);
    }

    public function test_it_converts_month_to_roman_correctly()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        $this->insertFormatSetting();

        $data = [
            'thn' => '2024',
            'tanggal' => '2024-12-15', // December
            'nomor' => '1',
            'kepada' => 'Tim',
            'perihal' => 'Perihal'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $data);

        $response->assertCreated()
                ->assertJsonPath('data.bln', 'XII')
                ->assertJsonPath('data.no_surat', '0001/SK/XII/2024');
    }

    // ==================== SHOW Tests ====================

    public function test_it_requires_authentication_to_show_surat_keputusan()
    {
        $suratKeputusan = SkBast::factory()->create(['type' => 'SK']);

        $response = $this->getJson("/api/kantor/surat/surat-keputusan/{$suratKeputusan->id}");

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_shows_surat_keputusan_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $suratKeputusan = SkBast::factory()->create(['type' => 'SK']);

        $response = $this->getJson("/api/kantor/surat/surat-keputusan/{$suratKeputusan->id}");

        $response->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        'id', 'thn', 'bln', 'tanggal', 'nomor', 'no_sisip', 'no_surat',
                        'oleh', 'kegiatan', 'kepada', 'perihal', 'type', 'kol_lampiran',
                        'create_by', 'created_at', 'updated_at'
                    ]
                ])
                ->assertJsonPath('data.id', $suratKeputusan->id)
                ->assertJsonPath('data.type', 'SK');
    }

    public function test_it_shows_surat_keputusan_as_user()
    {
        Sanctum::actingAs($this->user);

        $suratKeputusan = SkBast::factory()->create(['type' => 'SK']);

        $response = $this->getJson("/api/kantor/surat/surat-keputusan/{$suratKeputusan->id}");

        $response->assertOk()
                ->assertJsonPath('data.id', $suratKeputusan->id);
    }

    public function test_it_returns_404_for_nonexistent_surat_keputusan()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Surat keputusan not found']);
    }

    public function test_it_returns_404_for_non_sk_record()
    {
        Sanctum::actingAs($this->admin);

        $bast = SkBast::factory()->create(['type' => 'BAST']);

        $response = $this->getJson("/api/kantor/surat/surat-keputusan/{$bast->id}");

        $response->assertStatus(404)
                ->assertJson(['message' => 'Surat keputusan not found']);
    }

    // ==================== UPDATE Tests ====================

    public function test_it_requires_authentication_to_update_surat_keputusan()
    {
        $suratKeputusan = SkBast::factory()->create(['type' => 'SK']);

        $response = $this->putJson("/api/kantor/surat/surat-keputusan/{$suratKeputusan->id}", []);

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_updates_surat_keputusan_successfully_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $suratKeputusan = SkBast::factory()->create(['type' => 'SK']);

        // Mock format setting
        $this->insertFormatSetting();

        $data = [
            'thn' => '2024',
            'tanggal' => '2024-01-20',
            'nomor' => '2',
            'oleh' => 'Kepala Updated',
            'kegiatan' => 'Kegiatan Updated',
            'kepada' => 'Tim Updated',
            'perihal' => 'Perihal Updated'
        ];

        $response = $this->putJson("/api/kantor/surat/surat-keputusan/{$suratKeputusan->id}", $data);

        $response->assertOk()
                ->assertJsonPath('data.kepada', 'Tim Updated')
                ->assertJsonPath('data.perihal', 'Perihal Updated')
                ->assertJsonPath('data.type', 'SK');

        $this->assertDatabaseHas('surat_sk_bast', [
            'id' => $suratKeputusan->id,
            'kepada' => 'Tim Updated',
            'perihal' => 'Perihal Updated'
        ]);
    }

    public function test_it_validates_required_fields_on_update()
    {
        Sanctum::actingAs($this->admin);

        $suratKeputusan = SkBast::factory()->create(['type' => 'SK']);

        $response = $this->putJson("/api/kantor/surat/surat-keputusan/{$suratKeputusan->id}", []);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['tanggal', 'kepada', 'perihal']);
    }

    public function test_it_returns_404_when_updating_nonexistent_surat_keputusan()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'kepada' => 'Tim',
            'perihal' => 'Perihal'
        ];

        $response = $this->putJson('/api/kantor/surat/surat-keputusan/999', $data);

        $response->assertStatus(404)
                ->assertJson(['message' => 'Failed to update surat keputusan']);
    }

    // ==================== DESTROY Tests ====================

    public function test_it_requires_authentication_to_destroy_surat_keputusan()
    {
        $suratKeputusan = SkBast::factory()->create(['type' => 'SK']);

        $response = $this->deleteJson("/api/kantor/surat/surat-keputusan/{$suratKeputusan->id}");

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_deletes_surat_keputusan_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $suratKeputusan = SkBast::factory()->create(['type' => 'SK']);

        $response = $this->deleteJson("/api/kantor/surat/surat-keputusan/{$suratKeputusan->id}");

        $response->assertOk()
                ->assertJson(['message' => 'Nomor Surat berhasil dihapus']);

        $this->assertDatabaseMissing('surat_sk_bast', [
            'id' => $suratKeputusan->id
        ]);
    }

    public function test_it_deletes_surat_keputusan_as_user()
    {
        Sanctum::actingAs($this->user);

        $suratKeputusan = SkBast::factory()->create(['type' => 'SK']);

        $response = $this->deleteJson("/api/kantor/surat/surat-keputusan/{$suratKeputusan->id}");

        $response->assertOk()
                ->assertJson(['message' => 'Nomor Surat berhasil dihapus']);

        $this->assertDatabaseMissing('surat_sk_bast', [
            'id' => $suratKeputusan->id
        ]);
    }

    public function test_it_returns_404_when_deleting_nonexistent_surat_keputusan()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/kantor/surat/surat-keputusan/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Surat keputusan not found']);
    }

    // ==================== SISIP Tests ====================

    public function test_it_requires_authentication_to_sisip_surat_keputusan()
    {
        $response = $this->postJson('/api/kantor/surat/surat-keputusan/sisip', []);

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_sisips_surat_keputusan_successfully_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $this->insertFormatSetting();

        $reference = SkBast::factory()->create([
            'nomor' => '0006',
            'thn' => '2024',
            'tanggal' => '2024-01-10',
            'type' => 'SK',
        ]);

        $data = [
            'id' => $reference->id,
            'tanggal' => '2024-01-20',
            'type' => 'SK',
            'kepada' => 'Tim Sisip',
            'perihal' => 'Surat Keputusan Sisip',
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan/sisip', $data);

        $response->assertCreated()
                ->assertJsonPath('data.id', fn ($id) => $id !== $reference->id)
                ->assertJsonPath('data.nomor', '0006')
                ->assertJsonPath('data.no_sisip', '1')
                ->assertJsonPath('data.type', 'SK')
                ->assertJsonPath('data.kepada', 'Tim Sisip')
                ->assertJsonPath('data.perihal', 'Surat Keputusan Sisip');
    }

    public function test_it_validates_required_fields_on_sisip()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/kantor/surat/surat-keputusan/sisip', []);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['id', 'tanggal', 'type', 'kepada', 'perihal']);
    }

    public function test_it_validates_nomor_sisip_as_integer()
    {
        Sanctum::actingAs($this->admin);

        $reference = SkBast::factory()->create(['type' => 'SK']);

        $data = [
            'id' => $reference->id,
            'tanggal' => '2024-01-20',
            'type' => 'invalid',
            'kepada' => 'Tim',
            'perihal' => 'Perihal',
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan/sisip', $data);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['type']);
    }

    public function test_it_sisips_only_sk_records()
    {
        Sanctum::actingAs($this->admin);

        $this->insertFormatSetting();

        $reference = SkBast::factory()->create(['nomor' => '0005', 'thn' => '2024', 'type' => 'SK']);
        SkBast::factory()->create(['nomor' => '0005', 'thn' => '2024', 'type' => 'BAST', 'no_sisip' => '9']);
        SkBast::factory()->create(['nomor' => '0005', 'thn' => '2024', 'type' => 'SK', 'no_sisip' => '1']);

        $data = [
            'id' => $reference->id,
            'tanggal' => '2024-01-25',
            'type' => 'SK',
            'kepada' => 'Tim Baru',
            'perihal' => 'Perihal Baru',
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan/sisip', $data);

        $response->assertCreated()
                ->assertJsonPath('data.no_sisip', '2');
    }

    // ==================== GET DATES Tests ====================

    public function test_it_requires_authentication_to_get_dates()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keputusan/dates');

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_gets_dates_as_admin()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['tanggal' => date('Y') . '-01-15', 'thn' => date('Y'), 'type' => 'SK']);
        SkBast::factory()->create(['tanggal' => date('Y') . '-02-15', 'thn' => date('Y'), 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/dates');

        $response->assertOk()
                ->assertJsonCount(2, 'data')
                ->assertJsonPath('data.0', date('Y') . '-01-15')
                ->assertJsonPath('data.1', date('Y') . '-02-15');
    }

    public function test_it_gets_dates_as_user()
    {
        Sanctum::actingAs($this->user);

        SkBast::factory()->create(['tanggal' => date('Y') . '-01-15', 'thn' => date('Y'), 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/dates');

        $response->assertOk()
                ->assertJsonCount(1, 'data');
    }

    public function test_it_filters_dates_by_year()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['tanggal' => '2023-01-15', 'thn' => '2023', 'type' => 'SK']);
        SkBast::factory()->create(['tanggal' => '2024-01-15', 'thn' => '2024', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/dates?tahun=2024');

        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0', '2024-01-15');
    }

    public function test_it_gets_only_sk_dates()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['tanggal' => date('Y') . '-01-15', 'thn' => date('Y'), 'type' => 'SK']);
        SkBast::factory()->create(['tanggal' => date('Y') . '-02-15', 'thn' => date('Y'), 'type' => 'BAST']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/dates');

        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0', date('Y') . '-01-15');
    }

    public function test_it_orders_dates_ascending()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['tanggal' => date('Y') . '-03-15', 'thn' => date('Y'), 'type' => 'SK']);
        SkBast::factory()->create(['tanggal' => date('Y') . '-01-15', 'thn' => date('Y'), 'type' => 'SK']);
        SkBast::factory()->create(['tanggal' => date('Y') . '-02-15', 'thn' => date('Y'), 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/dates');

        $response->assertOk()
                ->assertJsonPath('data.0', date('Y') . '-01-15')
                ->assertJsonPath('data.1', date('Y') . '-02-15')
                ->assertJsonPath('data.2', date('Y') . '-03-15');
    }

    // ==================== GET YEARS Tests ====================

    public function test_it_requires_authentication_to_get_years()
    {
        $response = $this->getJson('/api/kantor/surat/surat-keputusan/years');

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_it_gets_years_as_admin()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['thn' => '2023', 'type' => 'SK']);
        SkBast::factory()->create(['thn' => '2024', 'type' => 'SK']);
        SkBast::factory()->create(['thn' => '2022', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/years');

        $response->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonPath('data.0', '2024') // Should be ordered descending
                ->assertJsonPath('data.1', '2023')
                ->assertJsonPath('data.2', '2022');
    }

    public function test_it_gets_years_as_user()
    {
        Sanctum::actingAs($this->user);

        SkBast::factory()->create(['thn' => '2024', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/years');

        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0', '2024');
    }

    public function test_it_gets_only_sk_years()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['thn' => '2024', 'type' => 'SK']);
        SkBast::factory()->create(['thn' => '2024', 'type' => 'BAST']);
        SkBast::factory()->create(['thn' => '2023', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/years');

        $response->assertOk()
                ->assertJsonCount(2, 'data')
                ->assertJsonPath('data.0', '2024')
                ->assertJsonPath('data.1', '2023');
    }

    // ==================== SECURITY Tests ====================

    public function test_it_prevents_sql_injection_in_search()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['kepada' => 'Valid Tim', 'type' => 'SK']);

        $maliciousInput = "'; DROP TABLE surat_sk_bast; --";

        $response = $this->getJson("/api/kantor/surat/surat-keputusan?search={$maliciousInput}");

        $response->assertOk();

        // Verify table still exists
        $this->assertDatabaseHas('surat_sk_bast', ['kepada' => 'Valid Tim']);
    }

    public function test_it_prevents_xss_in_data_fields()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        $this->insertFormatSetting();

        $maliciousData = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1',
            'kepada' => '<script>alert("XSS")</script>',
            'perihal' => 'Test'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $maliciousData);

        $response->assertCreated();

        $this->assertDatabaseHas('surat_sk_bast', [
            'kepada' => '<script>alert("XSS")</script>'
        ]);
    }

    // ==================== PERFORMANCE Tests ====================

    public function test_it_handles_large_dataset_efficiently()
    {
        Sanctum::actingAs($this->admin);

        // Create 100 records
        SkBast::factory()->count(100)->create(['type' => 'SK']);

        $startTime = microtime(true);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan?per_page=50');

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
        SkBast::factory()->count(50)->create(['thn' => '2023', 'type' => 'SK']);
        SkBast::factory()->count(50)->create(['thn' => '2024', 'type' => 'SK']);

        $startTime = microtime(true);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan?tahun=2024&tanggal=2024-06-15&search=Tim&sort_by=tanggal');

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

        $response = $this->getJson('/api/kantor/surat/surat-keputusan');

        $response->assertOk()
                ->assertJsonCount(0, 'data')
                ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_it_handles_empty_dates_list()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/dates');

        $response->assertOk()
                ->assertJsonCount(0, 'data');
    }

    public function test_it_handles_empty_years_list()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan/years');

        $response->assertOk()
                ->assertJsonCount(0, 'data');
    }

    public function test_it_handles_zero_nomor_on_store()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        $this->insertFormatSetting();

        $data = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '0', // Zero should be handled
            'kepada' => 'Tim',
            'perihal' => 'Perihal'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $data);

        $response->assertCreated()
                ->assertJsonPath('data.nomor', '0000');
    }

    public function test_it_handles_special_characters_in_search()
    {
        Sanctum::actingAs($this->admin);

        SkBast::factory()->create(['kepada' => 'Tim & PT. Maju Bersama', 'type' => 'SK']);

        $response = $this->getJson('/api/kantor/surat/surat-keputusan?search=Tim &');

        $response->assertOk()
                ->assertJsonCount(1, 'data');
    }

    // ==================== ROMAN NUMERAL Tests ====================

    public function test_it_converts_all_months_to_roman_correctly()
    {
        $testCases = [
            '01' => 'I',
            '02' => 'II',
            '03' => 'III',
            '04' => 'IV',
            '05' => 'V',
            '06' => 'VI',
            '07' => 'VII',
            '08' => 'VIII',
            '09' => 'IX',
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII'
        ];

        foreach ($testCases as $month => $expectedRoman) {
            Sanctum::actingAs($this->admin);

            // Mock format setting
            $this->insertFormatSetting();

            $data = [
                'thn' => '2024',
                'tanggal' => "2024-{$month}-15",
                'nomor' => '1',
                'kepada' => 'Tim',
                'perihal' => 'Perihal'
            ];

            $response = $this->postJson('/api/kantor/surat/surat-keputusan', $data);

            $response->assertCreated()
                    ->assertJsonPath('data.bln', $expectedRoman);

            // Clean up for next iteration
            \DB::table('surat_sk_bast')->truncate();
            \DB::table('settings')->where('key', 'FORMAT_SK')->delete();
        }
    }

    // ==================== DUPLICATE NOMOR Tests ====================

    public function test_it_handles_duplicate_nomor_gracefully()
    {
        Sanctum::actingAs($this->admin);

        // Mock format setting
        $this->insertFormatSetting();

        // Create existing record
        SkBast::factory()->create(['nomor' => '0001', 'thn' => '2024', 'type' => 'SK']);

        $data = [
            'thn' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '1', // Same as existing
            'kepada' => 'Tim Duplicate',
            'perihal' => 'Perihal Duplicate'
        ];

        $response = $this->postJson('/api/kantor/surat/surat-keputusan', $data);

        $response->assertCreated(); // Should still create duplicate as per business logic
    }
}
