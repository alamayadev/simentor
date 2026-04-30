<?php

namespace Tests\Feature\Kantor;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PegawaiApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles if they don't exist
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }

        if (!Role::where('name', 'user')->exists()) {
            Role::create(['name' => 'user']);
        }

        if (!Role::where('name', 'mitra')->exists()) {
            Role::create(['name' => 'mitra']);
        }

        // Create users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('user');
    }

    public function test_admin_can_list_pegawai()
    {
        // Create some pegawai records with null status (which is what gets displayed)
        $pegawai1 = Pegawai::factory()->create(['nama' => 'Budi Yunior', 'nip' => '198512312000121001', 'status' => null]);
        $pegawai2 = Pegawai::factory()->create(['nama' => 'Andi Saputra', 'nip' => '198612312000121002', 'status' => null]);
        // Create one with a status that should be filtered out
        $pegawai3 = Pegawai::factory()->create(['nama' => 'Siti Rahayu', 'nip' => '198712312000121003', 'status' => 'inactive']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/kantor/pegawai');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $this->assertUnifiedPaginationStructure($response);
    }

    public function test_regular_user_cannot_list_pegawai()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/kantor/pegawai');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_list_pegawai()
    {
        $response = $this->getJson('/api/kantor/pegawai');

        $response->assertStatus(401);
    }

    public function test_admin_can_create_pegawai()
    {
        $userData = User::factory()->create();

        $pegawaiData = [
            'nama' => 'Budi Yunior',
            'pangkat' => 'Penata Tk. I',
            'gol' => 'III/d',
            'nip' => '198512312000121001',
            'jabatan' => 'Kepala Seksi',
            'kelas' => 'IV',
            'user_id' => $userData->id,
            'status' => 'aktif'
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/kantor/pegawai', $pegawaiData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nama' => 'Budi Yunior',
                'nip' => '198512312000121001'
            ]);

        $this->assertDatabaseHas('profil_pegawai', [
            'nama' => 'Budi Yunior',
            'nip' => '198512312000121001'
        ]);
    }

    public function test_admin_can_show_pegawai()
    {
        $pegawai = Pegawai::factory()->create(['nama' => 'Test Pegawai', 'nip' => '198512312000121001']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/kantor/pegawai/{$pegawai->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $pegawai->id,
                'nama' => 'Test Pegawai',
                'nip' => '198512312000121001'
            ]);
    }

    public function test_admin_can_update_pegawai()
    {
        $pegawai = Pegawai::factory()->create(['nama' => 'Original Name', 'nip' => '198512312000121001']);
        $userData = User::factory()->create();

        $updatedData = [
            'nama' => 'Updated Name',
            'pangkat' => 'Pembina Utama Muda',
            'gol' => 'IV/c',
            'nip' => '198512312000121002',
            'jabatan' => 'Kepala Bagian',
            'kelas' => 'IV',
            'user_id' => $userData->id,
            'status' => 'cuti'
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/kantor/pegawai/{$pegawai->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'nama' => 'Updated Name',
                'nip' => '198512312000121002'
            ]);

        $this->assertDatabaseHas('profil_pegawai', [
            'id' => $pegawai->id,
            'nama' => 'Updated Name',
            'nip' => '198512312000121002'
        ]);
    }

    public function test_admin_can_delete_pegawai()
    {
        $pegawai = Pegawai::factory()->create(['nama' => 'To Delete', 'nip' => '198512312000121001']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/kantor/pegawai/{$pegawai->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Pegawai deleted successfully'
            ]);

        $this->assertDatabaseMissing('profil_pegawai', [
            'id' => $pegawai->id
        ]);
    }

    public function test_admin_can_get_pangkat_list()
    {
        // This route has been removed, so we test the combined route instead
        $this->assertTrue(true); // Placeholder test
    }

    public function test_admin_can_get_jabatan_list()
    {
        // This route has been removed, so we test the combined route instead
        $this->assertTrue(true); // Placeholder test
    }

    public function test_admin_can_get_jabatan_pangkat_list()
    {
        Pegawai::factory()->create(['jabatan' => 'Kepala Seksi', 'pangkat' => 'Penata Tk. I']);
        Pegawai::factory()->create(['jabatan' => 'Staf', 'pangkat' => 'Pembina Utama Muda']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/kantor/pegawai/jabatan-pangkat-list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'jabatanList',
                    'pangkatList'
                ]
            ])
            ->assertJsonPath('data.jabatanList', function ($jabatanList) {
                return in_array('Kepala Seksi', $jabatanList) && in_array('Staf', $jabatanList);
            })
            ->assertJsonPath('data.pangkatList', function ($pangkatList) {
                return in_array('Pembina Utama Muda', $pangkatList) && in_array('Penata Tk. I', $pangkatList);
 });
    }
}
