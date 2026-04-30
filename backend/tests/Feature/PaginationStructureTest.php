<?php

namespace Tests\Feature;

use App\Models\AssetIT;
use App\Models\DirektoriUsaha;
use App\Models\Kegiatan;
use App\Models\Link;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\BaseApiTestCase;

class PaginationStructureTest extends BaseApiTestCase
{
    /**
     * Test that page-based pagination structure is consistent across representative endpoints.
     */
    public function test_paginated_endpoints_use_the_unified_page_based_structure()
    {
        Role::findOrCreate('auditor-role', 'web');
        Permission::create(['name' => 'audit-records', 'guard_name' => 'web']);
        Kegiatan::factory()->count(3)->create(['tahun' => date('Y')]);
        Link::factory()->count(3)->create(['parent_id' => null]);
        AssetIT::factory()->count(3)->create();
        $this->seedDirektoriUsahaRecords();

        $endpoints = [
            '/api/admin/permissions',
            '/api/admin/roles',
            '/api/kantor/kegiatan',
            '/api/kantor/links',
            '/api/ipds/assets',
            '/api/kantor/direktori-usaha',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAsAdmin()
                ->getJson($endpoint . '?per_page=10');

            $response->assertOk();
            $this->assertUnifiedPaginationStructure($response);
            $this->assertSame(1, $response->json('meta.current_page'), "Endpoint {$endpoint} returned an unexpected current page");
            $this->assertSame($response->json('meta.last_page'), $response->json('pagination_info.total_page'), "Endpoint {$endpoint} has mismatched total pages");
            $this->assertSame($response->json('meta.total'), $response->json('pagination_info.total_records'), "Endpoint {$endpoint} has mismatched totals");
        }
    }

    /**
     * Test that the unified contract exposes page-based navigation and no legacy has_more field.
     */
    public function test_unified_pagination_contract_exposes_page_navigation_fields()
    {
        Kegiatan::factory()->count(11)->create(['tahun' => date('Y')]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/kegiatan?per_page=10');

        $response->assertOk();
        $this->assertUnifiedPaginationStructure($response);
        $this->assertArrayNotHasKey('has_more', $response->json('meta'));
        $this->assertPaginationHasNextPage($response);
        $this->assertSame(2, $response->json('meta.last_page'));
        $this->assertSame(11, $response->json('meta.total'));
    }

    private function seedDirektoriUsahaRecords(): void
    {
        foreach (range(1, 3) as $index) {
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
