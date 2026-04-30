<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to cek_georefs table for better search performance
        Schema::table('cek_georefs', function (Blueprint $table) {
            $table->index(['kec'], 'idx_cek_georefs_kec');
            $table->index(['desa'], 'idx_cek_georefs_desa');
            $table->index(['lokasi'], 'idx_cek_georefs_lokasi');
            $table->index(['kodename'], 'idx_cek_georefs_kodename');
            $table->index(['filename'], 'idx_cek_georefs_filename');
            // Note: kode index already exists as part of composite index in previous migration
        });

        // Add indexes to cek_scans table for better search performance
        Schema::table('cek_scans', function (Blueprint $table) {
            $table->index(['kec'], 'idx_cek_scans_kec');
            $table->index(['desa'], 'idx_cek_scans_desa');
            $table->index(['lokasi'], 'idx_cek_scans_lokasi');
            $table->index(['kodename'], 'idx_cek_scans_kodename');
            // Note: kode index already exists as part of composite index in previous migration
        });

        // Add indexes to alokasis table for better join performance
        Schema::table('alokasis', function (Blueprint $table) {
            // idsls index already exists from previous migration
            $table->index(['alokasi_scan'], 'idx_alokasis_alokasi_scan');
            $table->index(['alokasi_georef'], 'idx_alokasis_alokasi_georef');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from cek_georefs table
        Schema::table('cek_georefs', function (Blueprint $table) {
            $table->dropIndex('idx_cek_georefs_kec');
            $table->dropIndex('idx_cek_georefs_desa');
            $table->dropIndex('idx_cek_georefs_lokasi');
            $table->dropIndex('idx_cek_georefs_kodename');
            $table->dropIndex('idx_cek_georefs_filename');
        });

        // Remove indexes from cek_scans table
        Schema::table('cek_scans', function (Blueprint $table) {
            $table->dropIndex('idx_cek_scans_kec');
            $table->dropIndex('idx_cek_scans_desa');
            $table->dropIndex('idx_cek_scans_lokasi');
            $table->dropIndex('idx_cek_scans_kodename');
        });

        // Remove indexes from alokasis table
        Schema::table('alokasis', function (Blueprint $table) {
            $table->dropIndex('idx_alokasis_alokasi_scan');
            $table->dropIndex('idx_alokasis_alokasi_georef');
        });
    }
};
