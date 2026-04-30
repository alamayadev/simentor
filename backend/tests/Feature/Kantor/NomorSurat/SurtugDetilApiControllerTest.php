<?php

namespace Tests\Feature\Kantor\NomorSurat;

use App\Models\User;
use App\Models\SuratTugas;
use App\Models\SurtugDetil;
use App\Models\Pegawai;
use App\Models\Mitra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SurtugDetilApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $suratTugas;
    protected $pegawai;
    protected $mitra;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');

        // Create test data
        $this->suratTugas = SuratTugas::create([
            'tahun' => '2024',
            'tanggal' => '2024-01-15',
            'nomor' => '0001',
            'no_sisip' => null,
            'no_mix' => '0001',
            'no_surat' => '0001/ST-100/2024',
            'tanggal_indo' => '15 Januari 2024',
            'kode_klas' => '100',
            'kepada' => 'Tim Survei',
            'menimbang' => 'Dalam rangka pelaksanaan kegiatan',
            'uraian' => 'Melaksanakan survei lapangan',
            'file' => null,
            'created_by' => $this->user->id,
        ]);

        $this->pegawai = Pegawai::factory()->create();
        $this->mitra = Mitra::factory()->create();
    }

    public function test_can_list_surtug_detil()
    {
        $response = $this->getJson('/api/kantor/surat/surtug-detil');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
            ]);
    }

    public function test_can_list_surtug_detil_with_filters()
    {
        // Create a surtug detil
        SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'pegawai_id' => $this->pegawai->id,
            'mitra_id' => null,
            'grup_mitra' => null,
            'grup_pegawai' => 1,
            'penugasan_id' => null,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'tugas_sebagai' => 'Petugas Lapangan',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'jenis_kendaraan' => 'Motor',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
            'sppd' => false,
        ]);

        // Test filter by surtug_id
        $response = $this->getJson('/api/kantor/surat/surtug-detil?surtug_id=' . $this->suratTugas->id);
        $response->assertStatus(200);

        // Test filter by pegawai_id
        $response = $this->getJson('/api/kantor/surat/surtug-detil?pegawai_id=' . $this->pegawai->id);
        $response->assertStatus(200);
    }

    public function test_cannot_list_surtug_detil_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surtug-detil');

        $response->assertStatus(401);
    }

    public function test_can_store_surtug_detil()
    {
        $surtugDetilData = [
            'surtug_id' => $this->suratTugas->id,
            'pegawai_id' => $this->pegawai->id,
            'mitra_id' => null,
            'grup_mitra' => null,
            'grup_pegawai' => 1,
            'penugasan_id' => null,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'tugas_sebagai' => 'Petugas Lapangan',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'jenis_kendaraan' => 'Motor',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
            'sppd' => false,
        ];

        $response = $this->postJson('/api/kantor/surat/surtug-detil', $surtugDetilData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'surtug_id',
                    'pegawai_id',
                    'mitra_id',
                    'dasar',
                    'nama_kegiatan',
                    'hari',
                    'wilayah_kerja',
                    'tgl_mulai',
                    'no_dipa',
                    'isOrganik',
                    'sppd',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('surat_tugas_detil', [
            'surtug_id' => $this->suratTugas->id,
            'nama_kegiatan' => 'Sensus Penduduk',
        ]);
    }

    public function test_cannot_store_surtug_detil_without_authentication()
    {
        $surtugDetilData = [
            'surtug_id' => $this->suratTugas->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/surtug-detil', $surtugDetilData);

        $response->assertStatus(401);
    }

    public function test_validation_error_when_storing_surtug_detil_with_invalid_data()
    {
        $invalidData = [
            'surtug_id' => null,
            'dasar' => '',
            'nama_kegiatan' => '',
            'hari' => 'invalid',
            'wilayah_kerja' => '',
            'tgl_mulai' => 'invalid-date',
            'no_dipa' => '',
            'isOrganik' => 'invalid',
        ];

        $response = $this->postJson('/api/kantor/surat/surtug-detil', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    public function test_cannot_store_surtug_detil_with_invalid_surtug_id()
    {
        $surtugDetilData = [
            'surtug_id' => 99999, // Non-existent surtug_id
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ];

        $response = $this->postJson('/api/kantor/surat/surtug-detil', $surtugDetilData);

        $this->assertContains($response->getStatusCode(), [404, 422]);
    }

    public function test_can_show_surtug_detil()
    {
        $surtugDetil = SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'pegawai_id' => $this->pegawai->id,
            'mitra_id' => null,
            'grup_mitra' => null,
            'grup_pegawai' => 1,
            'penugasan_id' => null,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'tugas_sebagai' => 'Petugas Lapangan',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'jenis_kendaraan' => 'Motor',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
            'sppd' => false,
        ]);

        $response = $this->getJson('/api/kantor/surat/surtug-detil/' . $surtugDetil->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'surtug_id',
                    'pegawai_id',
                    'mitra_id',
                    'dasar',
                    'nama_kegiatan',
                    'hari',
                    'wilayah_kerja',
                    'tgl_mulai',
                    'no_dipa',
                    'isOrganik',
                    'sppd',
                    'pegawai',
                    'mitra',
                    'nomor',
                ],
            ]);
    }

    public function test_cannot_show_surtug_detil_without_authentication()
    {
        $surtugDetil = SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ]);

        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surtug-detil/' . $surtugDetil->id);

        $response->assertStatus(401);
    }

    public function test_cannot_show_nonexistent_surtug_detil()
    {
        $response = $this->getJson('/api/kantor/surat/surtug-detil/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_surtug_detil()
    {
        $surtugDetil = SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'pegawai_id' => $this->pegawai->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ]);

        $updateData = [
            'surtug_id' => $this->suratTugas->id,
            'pegawai_id' => $this->pegawai->id,
            'dasar' => 'Peraturan BPS No. 2 Tahun 2024',
            'nama_kegiatan' => 'Sensus Ekonomi',
            'hari' => 10,
            'wilayah_kerja' => 'Kecamatan B',
            'tgl_mulai' => '2024-02-01',
            'no_dipa' => 'DIPA-2024-002',
            'isOrganik' => false,
        ];

        $response = $this->putJson('/api/kantor/surat/surtug-detil/' . $surtugDetil->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);

        $this->assertDatabaseHas('surat_tugas_detil', [
            'id' => $surtugDetil->id,
            'nama_kegiatan' => 'Sensus Ekonomi',
            'hari' => 10,
        ]);
    }

    public function test_cannot_update_surtug_detil_without_authentication()
    {
        $surtugDetil = SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ]);

        $updateData = [
            'nama_kegiatan' => 'Updated Sensus',
        ];

        $response = $this->actingAsGuest()->putJson('/api/kantor/surat/surtug-detil/' . $surtugDetil->id, $updateData);

        $response->assertStatus(401);
    }

    public function test_validation_error_when_updating_surtug_detil_with_invalid_data()
    {
        $surtugDetil = SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ]);

        $invalidData = [
            'surtug_id' => null,
            'dasar' => '',
            'nama_kegiatan' => '',
            'hari' => 'invalid',
            'wilayah_kerja' => '',
            'tgl_mulai' => 'invalid-date',
            'no_dipa' => '',
            'isOrganik' => 'invalid',
        ];

        $response = $this->putJson('/api/kantor/surat/surtug-detil/' . $surtugDetil->id, $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    public function test_cannot_update_nonexistent_surtug_detil()
    {
        $updateData = [
            'surtug_id' => $this->suratTugas->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ];

        $response = $this->putJson('/api/kantor/surat/surtug-detil/99999', $updateData);

        $response->assertStatus(404);
    }

    public function test_can_delete_surtug_detil()
    {
        $surtugDetil = SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ]);

        $response = $this->deleteJson('/api/kantor/surat/surtug-detil/' . $surtugDetil->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Detail surat tugas berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('surat_tugas_detil', [
            'id' => $surtugDetil->id,
        ]);
    }

    public function test_cannot_delete_surtug_detil_without_authentication()
    {
        $surtugDetil = SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ]);

        $response = $this->actingAsGuest()->deleteJson('/api/kantor/surat/surtug-detil/' . $surtugDetil->id);

        $response->assertStatus(401);
    }

    public function test_cannot_delete_nonexistent_surtug_detil()
    {
        $response = $this->deleteJson('/api/kantor/surat/surtug-detil/99999');

        $response->assertStatus(404);
    }

    public function test_can_get_surtug_detil_by_surtug_id()
    {
        // Create multiple surtug detil for the same surat tugas
        SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'pegawai_id' => $this->pegawai->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => true,
        ]);

        SurtugDetil::create([
            'surtug_id' => $this->suratTugas->id,
            'mitra_id' => $this->mitra->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Ekonomi',
            'hari' => 3,
            'wilayah_kerja' => 'Kecamatan B',
            'tgl_mulai' => '2024-01-20',
            'no_dipa' => 'DIPA-2024-001',
            'isOrganik' => false,
        ]);

        $response = $this->getJson('/api/kantor/surat/surtug-detil/surtug/' . $this->suratTugas->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'surtug_id',
                        'pegawai_id',
                        'mitra_id',
                        'dasar',
                        'nama_kegiatan',
                        'hari',
                        'wilayah_kerja',
                        'tgl_mulai',
                        'no_dipa',
                        'isOrganik',
                        'sppd',
                        'pegawai',
                        'mitra',
                    ],
                ],
            ]);

        // Check that we got 2 records
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_cannot_get_surtug_detil_by_surtug_id_without_authentication()
    {
        $response = $this->actingAsGuest()->getJson('/api/kantor/surat/surtug-detil/surtug/' . $this->suratTugas->id);

        $response->assertStatus(401);
    }

    public function test_cannot_get_surtug_detil_by_nonexistent_surtug_id()
    {
        $response = $this->getJson('/api/kantor/surat/surtug-detil/surtug/99999');

        $response->assertStatus(404);
    }

    public function test_can_bulk_create_surtug_detil_for_mitra()
    {
        // Create a kegiatan for testing
        $kegiatan = \App\Models\Kegiatan::create([
            'kode' => 'TEST001',
            'nama' => 'Test Kegiatan',
            'deskripsi' => 'Test kegiatan description',
        ]);

        // Create penugasan for each mitra
        $penugasan1 = \App\Models\Penugasan::create([
            'kegiatan_id' => $kegiatan->id,
            'mitra_id' => $this->mitra->id,
            'volume' => 10,
            'nilai' => 1000000,
            'bln_bayar' => '2024-01-15',
            'created_by' => $this->user->id,
        ]);

        $mitra2 = Mitra::factory()->create();
        $penugasan2 = \App\Models\Penugasan::create([
            'kegiatan_id' => $kegiatan->id,
            'mitra_id' => $mitra2->id,
            'volume' => 5,
            'nilai' => 500000,
            'bln_bayar' => '2024-01-15',
            'created_by' => $this->user->id,
        ]);

        $bulkData = [
            'surtug_id' => $this->suratTugas->id,
            'mitra_ids' => [$this->mitra->id, $mitra2->id],
            'kegiatan_id' => $kegiatan->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'tugas_sebagai' => 'Petugas Lapangan',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
        ];

        $response = $this->postJson('/api/kantor/surat/surtug-detil/bulk-mitra', $bulkData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'created',
                    'failed',
                    'details',
                    'failed_details',
                ],
            ]);

        // Verify that 2 records were created
        $this->assertDatabaseCount('surat_tugas_detil', 2);
        
        // Verify the created records have the correct values
        $this->assertDatabaseHas('surat_tugas_detil', [
            'surtug_id' => $this->suratTugas->id,
            'mitra_id' => $this->mitra->id,
            'penugasan_id' => $penugasan1->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'isOrganik' => false,
            'sppd' => false,
        ]);
    }

    public function test_cannot_bulk_create_surtug_detil_without_authentication()
    {
        $bulkData = [
            'surtug_id' => $this->suratTugas->id,
            'mitra_ids' => [$this->mitra->id],
            'kegiatan_id' => 1,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
        ];

        $response = $this->actingAsGuest()->postJson('/api/kantor/surat/surtug-detil/bulk-mitra', $bulkData);

        $response->assertStatus(401);
    }

    public function test_validation_error_when_bulk_creating_with_invalid_data()
    {
        $invalidData = [
            'surtug_id' => null,
            'mitra_ids' => [],
            'kegiatan_id' => null,
            'dasar' => '',
            'nama_kegiatan' => '',
            'hari' => 'invalid',
            'wilayah_kerja' => '',
            'tgl_mulai' => 'invalid-date',
            'no_dipa' => '',
        ];

        $response = $this->postJson('/api/kantor/surat/surtug-detil/bulk-mitra', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    public function test_cannot_bulk_create_with_invalid_surtug_id()
    {
        $bulkData = [
            'surtug_id' => 99999,
            'mitra_ids' => [$this->mitra->id],
            'kegiatan_id' => 1,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
        ];

        $response = $this->postJson('/api/kantor/surat/surtug-detil/bulk-mitra', $bulkData);

        $this->assertContains($response->getStatusCode(), [404, 422]);
    }

    public function test_cannot_bulk_create_with_empty_mitra_ids()
    {
        $bulkData = [
            'surtug_id' => $this->suratTugas->id,
            'mitra_ids' => [],
            'kegiatan_id' => 1,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
        ];

        $response = $this->postJson('/api/kantor/surat/surtug-detil/bulk-mitra', $bulkData);

        $response->assertStatus(422);
    }

    public function test_bulk_create_handles_partial_failures()
    {
        // Create a kegiatan
        $kegiatan = \App\Models\Kegiatan::create([
            'kode' => 'TEST002',
            'nama' => 'Test Kegiatan 2',
            'deskripsi' => 'Test kegiatan description 2',
        ]);

        $penugasan = \App\Models\Penugasan::create([
            'kegiatan_id' => $kegiatan->id,
            'mitra_id' => $this->mitra->id,
            'volume' => 10,
            'nilai' => 1000000,
            'bln_bayar' => '2024-01-15',
            'created_by' => $this->user->id,
        ]);

        // Use a non-existent mitra_id to trigger a failure
        $bulkData = [
            'surtug_id' => $this->suratTugas->id,
            'mitra_ids' => [$this->mitra->id, 99999], // 99999 doesn't exist
            'kegiatan_id' => $kegiatan->id,
            'dasar' => 'Peraturan BPS No. 1 Tahun 2024',
            'nama_kegiatan' => 'Sensus Penduduk',
            'tugas_sebagai' => 'Petugas Lapangan',
            'hari' => 5,
            'wilayah_kerja' => 'Kecamatan A',
            'tgl_mulai' => '2024-01-15',
            'no_dipa' => 'DIPA-2024-001',
        ];

        $response = $this->postJson('/api/kantor/surat/surtug-detil/bulk-mitra', $bulkData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Should have 1 created and 1 failed
        $data = $response->json('data');
        $this->assertEquals(1, $data['created']);
        $this->assertEquals(1, $data['failed']);
        $this->assertCount(1, $data['details']);
        $this->assertCount(1, $data['failed_details']);
    }
}
