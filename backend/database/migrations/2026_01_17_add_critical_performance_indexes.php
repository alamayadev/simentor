<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Composite index for penugasan table
        // Commonly filtered by mitra_id and bln_bayar in SPK/BAST endpoints
        Schema::table('penugasan', function (Blueprint $table) {
            $table->index(['mitra_id', 'bln_bayar'], 'penugasan_mitra_bulan_index');
        });
        
        // Composite index for monitoring_kegiatan table
        // Commonly filtered by kegiatan_id, kec_id, and desa_id
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            $table->index(['kegiatan_id', 'kec_id', 'desa_id'], 'monitoring_kegiatan_filters_index');
        });
        
        // Index for data_surtug_detil table
        // Frequently queried by surtug_id
        Schema::table('data_surtug_detil', function (Blueprint $table) {
            $table->index('surtug_id', 'surtug_detil_index');
        });
    }

    public function down(): void
    {
        Schema::table('penugasan', function (Blueprint $table) {
            $table->dropIndex('penugasan_mitra_bulan_index');
        });
        
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            $table->dropIndex('monitoring_kegiatan_filters_index');
        });
        
        Schema::table('data_surtug_detil', function (Blueprint $table) {
            $table->dropIndex('surtug_detil_index');
        });
    }
};
