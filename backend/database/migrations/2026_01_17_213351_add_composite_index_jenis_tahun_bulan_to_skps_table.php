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
        Schema::table('skps', function (Blueprint $table) {
            // Add composite index for performance optimization on stat2 endpoint
            // This index improves queries that filter by jenis, tahun, and bulan columns
            $table->index(['jenis', 'tahun', 'bulan'], 'skp_jenis_tahun_bulan_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skps', function (Blueprint $table) {
            // Remove the composite index
            $table->dropIndex('skp_jenis_tahun_bulan_index');
        });
    }
};
