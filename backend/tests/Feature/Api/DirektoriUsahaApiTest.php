<?php

namespace Tests\Feature\Api;

use App\Models\DirektoriUsaha;

class DirektoriUsahaApiTest extends BaseApiTestCase
{
    public function test_index_returns_unified_pagination_structure(): void
    {
        $this->seedDirektoriUsahaRecords(12);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/direktori-usaha?per_page=5');

        $response->assertOk();
        $this->assertUnifiedPaginationStructure($response);
        $this->assertSame(1, $response->json('meta.current_page'));
        $this->assertSame(5, $response->json('meta.per_page'));
        $this->assertSame(5, $response->json('meta.count'));
        $this->assertSame(12, $response->json('meta.total'));
        $this->assertSame(3, $response->json('pagination_info.total_page'));
        $this->assertSame(12, $response->json('pagination_info.total_records'));
        $this->assertPaginationHasNextPage($response);
    }

    public function test_index_supports_filters_while_preserving_unified_pagination_structure(): void
    {
        $this->seedDirektoriUsahaRecords();

        DirektoriUsaha::create([
            'idsbr' => 'SBR-CV',
            'nama_usaha' => 'CV Sukses Bersama',
            'alamat_usaha' => 'Jalan Melati 99',
            'kode_wilayah' => '3215',
            'kdprov' => '32',
            'kdkab' => '15',
            'kdkec' => '099',
            'kddesa' => '001',
            'nmprov' => 'Jawa Barat',
            'nmkab' => 'Karawang',
            'nmkec' => 'Klari',
            'nmdesa' => 'Anggadita',
            'latlong_status' => 'valid',
            'name_similarity' => 95.00,
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/kantor/direktori-usaha?filter[search]=CV&filter[kdkec]=099&per_page=10');

        $response->assertOk();
        $this->assertUnifiedPaginationStructure($response);
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('CV Sukses Bersama', $response->json('data.0.nama_usaha'));
        $this->assertPaginationHasNoNextPage($response);
    }

    private function seedDirektoriUsahaRecords(int $count = 3): void
    {
        foreach (range(1, $count) as $index) {
            DirektoriUsaha::create([
                'idsbr' => 'SBR-' . $index,
                'nama_usaha' => 'Usaha Test ' . $index,
                'alamat_usaha' => 'Jalan Mawar ' . $index,
                'kode_wilayah' => '3215',
                'kdprov' => '32',
                'kdkab' => '15',
                'kdkec' => '051',
                'kddesa' => '008',
                'nmprov' => 'Jawa Barat',
                'nmkab' => 'Karawang',
                'nmkec' => 'Purwasari',
                'nmdesa' => 'Cengkong',
                'latitude' => null,
                'longitude' => null,
                'latlong_status' => 'invalid',
                'gcs_result' => null,
                'latitude_gc' => null,
                'longitude_gc' => null,
                'latlong_status_gc' => null,
                'hasilgc' => null,
                'name_similarity' => 100.00,
            ]);
        }
    }
}
