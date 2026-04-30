<?php

namespace Tests\Feature\Api\Ipds;

use App\Models\RawData;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Api\BaseApiTestCase;

class RawDataApiTest extends BaseApiTestCase
{
    use RefreshDatabase;

    private function requireFileinfoExtension(): void
    {
        if (! class_exists(\finfo::class)) {
            $this->markTestSkipped('PHP fileinfo extension is required for raw data file storage tests.');
        }
    }

    protected function setUp(): void
    {
        \Tests\TestCase::setUp();

        $this->admin = User::factory()->create();
        $this->organik = User::factory()->create();
        $this->mitra = User::factory()->create();
        $this->regularUser = User::factory()->create();

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'raw-data-view', 'guard_name' => 'web']);
        Permission::create(['name' => 'raw-data-create', 'guard_name' => 'web']);
        Permission::create(['name' => 'raw-data-update', 'guard_name' => 'web']);
        Permission::create(['name' => 'raw-data-delete', 'guard_name' => 'web']);
        
        // Also create for sanctum guard as fallback/primary depending on resolving
        Permission::create(['name' => 'raw-data-view', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'raw-data-create', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'raw-data-update', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'raw-data-delete', 'guard_name' => 'sanctum']);

        // Assign SANCTUM permissions to admin explicitly
        $this->admin->givePermissionTo(Permission::where('guard_name', 'sanctum')->get());

        // Assign SANCTUM permissions to organik (as user)
        $this->organik->givePermissionTo(Permission::where('guard_name', 'sanctum')->get());
    }

    public function test_it_requires_authentication_to_index_raw_data()
    {
        $response = $this->getJson('/api/ipds/raw-datas');

        $response->assertStatus(401);
    }

    public function test_it_lists_raw_data_as_admin()
    {
        Sanctum::actingAs($this->admin);

        RawData::factory()->count(3)->create();

        $response = $this->getJson('/api/ipds/raw-datas');

        $response->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'fungsi', 'nama', 'keterangan', 'file',
                            'created_at', 'updated_at'
                        ]
                    ],
                    'meta',
                    'links',
                    'pagination_info'
                ]);
    }

    public function test_it_lists_raw_data_as_user()
    {
        Sanctum::actingAs($this->organik);

        RawData::factory()->count(3)->create();

        $response = $this->getJson('/api/ipds/raw-datas');

        $response->assertOk()
                ->assertJsonCount(3, 'data');
    }

    public function test_it_paginates_raw_data()
    {
        Sanctum::actingAs($this->admin);

        RawData::factory()->count(20)->create();

        $response = $this->getJson('/api/ipds/raw-datas?per_page=5');

        $response->assertOk()
                ->assertJsonPath('meta.per_page', 5)
                ->assertJsonPath('meta.count', 5)
                ->assertJsonPath('pagination_info.total_records', 20)
                ->assertJsonPath('pagination_info.total_page', 4)
                ->assertJsonCount(5, 'data');

        $this->assertUnifiedPaginationStructure($response);
        $this->assertPaginationHasNextPage($response);
    }

    public function test_it_filters_raw_data_by_fungsi()
    {
        Sanctum::actingAs($this->admin);

        RawData::factory()->create(['fungsi' => 'Survei']);
        RawData::factory()->create(['fungsi' => 'Pengolahan']);

        $response = $this->getJson('/api/ipds/raw-datas?fungsi=Survei');

        $response->assertOk()
                ->assertJsonCount(1, 'data');
    }

    public function test_it_filters_raw_data_by_nama()
    {
        Sanctum::actingAs($this->admin);

        RawData::factory()->create(['nama' => 'Data Pencacahan']);
        RawData::factory()->create(['nama' => 'Data Pengolahan']);

        $response = $this->getJson('/api/ipds/raw-datas?nama=Pencacahan');

        $response->assertOk()
                ->assertJsonCount(1, 'data');
    }

    public function test_it_creates_raw_data()
    {
        $this->requireFileinfoExtension();

        Sanctum::actingAs($this->admin);

        Storage::fake('direct');

        $file = UploadedFile::fake()->create('data.zip', 1000, 'application/zip');

        $response = $this->postJson('/api/ipds/raw-datas', [
            'type' => 'Survei',
            'fungsi' => 'Survei',
            'nama' => 'Data Pencacahan',
            'keterangan' => 'Data hasil pencacahan',
            'file' => $file,
        ]);

        $response->assertCreated()
                ->assertJsonPath('data.type', 'Survei')
                ->assertJsonPath('data.fungsi', 'Survei')
                ->assertJsonPath('data.nama', 'Data Pencacahan')
                ->assertJsonPath('data.keterangan', 'Data hasil pencacahan');

        // Get the stored file path from the response
        $storedFilePath = $response->json('data.file');
        Storage::disk('direct')->assertExists($storedFilePath);
    }

    public function test_it_requires_authentication_to_create_raw_data()
    {
        $response = $this->postJson('/api/ipds/raw-datas', [
            'fungsi' => 'Survei',
            'nama' => 'Data Pencacahan',
        ]);

        $response->assertStatus(401);
    }

    public function test_it_shows_raw_data()
    {
        Sanctum::actingAs($this->admin);

        $rawData = RawData::factory()->create();

        $response = $this->getJson("/api/ipds/raw-datas/{$rawData->id}");

        $response->assertOk()
                ->assertJsonPath('data.id', $rawData->id);
    }

    public function test_it_returns_404_when_showing_nonexistent_raw_data()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/ipds/raw-datas/999');

        $response->assertStatus(404)
                ->assertJsonPath('message', 'Raw Data not found');
    }

    public function test_it_updates_raw_data()
    {
        $this->requireFileinfoExtension();

        Sanctum::actingAs($this->admin);

        Storage::fake('direct');

        $rawData = RawData::factory()->create();

        $file = UploadedFile::fake()->create('updated_data.zip', 1000, 'application/zip');

        $response = $this->putJson("/api/ipds/raw-datas/{$rawData->id}", [
            'fungsi' => 'Survei Updated',
            'nama' => 'Data Pencacahan Updated',
            'file' => $file,
        ]);

        $response->assertOk()
                ->assertJsonPath('data.fungsi', 'Survei Updated')
                ->assertJsonPath('data.nama', 'Data Pencacahan Updated');
        
        // Get the stored file path from the response
        $storedFilePath = $response->json('data.file');
        Storage::disk('direct')->assertExists($storedFilePath);
    }

    public function test_it_deletes_raw_data()
    {
        $this->requireFileinfoExtension();

        Sanctum::actingAs($this->admin);

        Storage::fake('direct');

        $rawData = RawData::factory()->create([
            'file' => 'raw_data/test_file.zip',
        ]);

        Storage::disk('direct')->put('raw_data/test_file.zip', 'test content');

        $response = $this->deleteJson("/api/ipds/raw-datas/{$rawData->id}");

        $response->assertOk()
                ->assertJsonPath('message', 'Raw Data deleted successfully');

        $this->assertDatabaseMissing('raw_datas', [
            'id' => $rawData->id,
        ]);
    }

    public function test_it_validates_file_type()
    {
        $this->requireFileinfoExtension();

        Sanctum::actingAs($this->admin);

        Storage::fake('direct');

        // Use .exe which is definitely not allowed
        $invalidFile = UploadedFile::fake()->create('invalid.exe', 1000, 'application/x-msdownload');

        $response = $this->postJson('/api/ipds/raw-datas', [
            'fungsi' => 'Survei',
            'nama' => 'Data Pencacahan',
            'file' => $invalidFile,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }
}
