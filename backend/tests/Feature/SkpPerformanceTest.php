<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SkpPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_skps_table_has_nama_index()
    {
        // PERFORMANCE FIX: Verify nama index exists for search queries
        // Used in list() search: ->where('nama', 'like', "%{$search}%")
        $this->assertTrue(Schema::hasIndex('skps', 'skp_nama_index'));
    }

    public function test_skps_table_has_tahun_index()
    {
        // PERFORMANCE FIX: Verify tahun index exists for filtering by year
        // Used in list() filter: ->where('tahun', $tahun)
        $this->assertTrue(Schema::hasIndex('skps', 'skp_tahun_index'));
    }

    public function test_skps_table_has_bulan_index()
    {
        // PERFORMANCE FIX: Verify bulan index exists for filtering by month
        // Used in list() filter: ->where('bulan', $bulan)
        $this->assertTrue(Schema::hasIndex('skps', 'skp_bulan_index'));
    }

    public function test_skps_table_has_user_id_index()
    {
        // PERFORMANCE FIX: Verify user_id index exists for user-specific queries
        // Used in dashboard: ->where('user_id', $user->id)
        $this->assertTrue(Schema::hasIndex('skps', 'skp_user_id_index'));
    }

    public function test_skps_table_has_jenis_index()
    {
        // PERFORMANCE FIX: Verify jenis index exists for filtering by SKP type
        // Used in dashboard: ->where('jenis', 'SKP Bulanan')
        $this->assertTrue(Schema::hasIndex('skps', 'skp_jenis_index'));
    }

    public function test_skps_table_has_created_at_index()
    {
        // PERFORMANCE FIX: Verify created_at index exists for sorting by creation date
        // Used in list(): ->orderBy('created_at', 'DESC')
        $this->assertTrue(Schema::hasIndex('skps', 'skp_created_at_index'));
    }

    public function test_skps_table_has_tahun_bulan_composite_index()
    {
        // PERFORMANCE FIX: Verify composite index for year + month queries
        // Used in list(): ->where('tahun', $tahun)->where('bulan', $bulan)
        $this->assertTrue(Schema::hasIndex('skps', 'skp_tahun_bulan_index'));
    }
}