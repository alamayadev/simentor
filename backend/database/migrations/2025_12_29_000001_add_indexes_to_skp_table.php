<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PERFORMANCE FIX: Add indexes to optimize search queries
     *
     * This migration adds indexes to skp table to improve query performance
     * for searches and filters on frequently queried columns:
     * - nama: Used in list() search functionality
     * - tahun: Used in multiple filters and groupings
     * - bulan: Used in monthly SKP queries
     * - user_id: Used for filtering SKP by user
     * - jenis: Used for filtering by SKP type
     * - created_at: Used for ordering and recent queries
     *
     * Indexes significantly improve query performance when searching or filtering
     * by these columns, especially as SKP records grow.
     *
     * @return void
     */
    public function up(): void
    {
        // Only add indexes if skps table exists
        if (!Schema::hasTable('skps')) {
            return;
        }

        Schema::table('skps', function (Blueprint $table) {
            // PERFORMANCE FIX: Add index on nama column
            // Used in list() search: ->where('nama', 'like', "%{$search}%")
            if (!Schema::hasIndex('skps', 'skp_nama_index')) {
                $table->index('nama', 'skp_nama_index');
            }

            // PERFORMANCE FIX: Add index on tahun column
            // Used in multiple queries: ->where('tahun', $selectedTahun)
            if (!Schema::hasIndex('skps', 'skp_tahun_index')) {
                $table->index('tahun', 'skp_tahun_index');
            }

            // PERFORMANCE FIX: Add index on bulan column
            // Used in monthly SKP queries: ->where('bulan', $selectedBulan)
            if (!Schema::hasIndex('skps', 'skp_bulan_index')) {
                $table->index('bulan', 'skp_bulan_index');
            }

            // PERFORMANCE FIX: Add index on user_id column
            // Used for filtering SKP by user: ->where('user_id', $userId)
            if (!Schema::hasIndex('skps', 'skp_user_id_index')) {
                $table->index('user_id', 'skp_user_id_index');
            }

            // PERFORMANCE FIX: Add index on jenis column
            // Used for filtering by SKP type: ->where('jenis', 'SKP Bulanan')
            if (!Schema::hasIndex('skps', 'skp_jenis_index')) {
                $table->index('jenis', 'skp_jenis_index');
            }

            // PERFORMANCE FIX: Add index on created_at column
            // Useful for ordering by creation date and recent queries
            if (!Schema::hasIndex('skps', 'skp_created_at_index')) {
                $table->index('created_at', 'skp_created_at_index');
            }

            // PERFORMANCE FIX: Add composite index for year+month queries
            // Used in stats queries: ->where('tahun', $year)->where('bulan', $month)
            if (!Schema::hasIndex('skps', 'skp_tahun_bulan_index')) {
                $table->index(['tahun', 'bulan'], 'skp_tahun_bulan_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('skps', function (Blueprint $table) {
            $table->dropIndex('skp_nama_index');
            $table->dropIndex('skp_tahun_index');
            $table->dropIndex('skp_bulan_index');
            $table->dropIndex('skp_user_id_index');
            $table->dropIndex('skp_jenis_index');
            $table->dropIndex('skp_created_at_index');
            $table->dropIndex('skp_tahun_bulan_index');
        });
    }
};
