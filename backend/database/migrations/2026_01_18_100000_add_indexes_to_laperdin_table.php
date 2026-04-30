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
        Schema::table('laporan_perjalanan_dinas', function (Blueprint $table) {
            // Index for default sorting by created_at
            if (!Schema::hasIndex('laporan_perjalanan_dinas', 'lpd_created_at_index')) {
                $table->index('created_at', 'lpd_created_at_index');
            }

            // Composite index for filtering by user and status (common query)
            if (!Schema::hasIndex('laporan_perjalanan_dinas', 'lpd_user_status_index')) {
                $table->index(['user_id', 'status'], 'lpd_user_status_index');
            }
            
            // Index for status alone if filtered globally
            if (!Schema::hasIndex('laporan_perjalanan_dinas', 'lpd_status_index')) {
                $table->index('status', 'lpd_status_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_perjalanan_dinas', function (Blueprint $table) {
            $table->dropIndex('lpd_created_at_index');
            $table->dropIndex('lpd_user_status_index');
            $table->dropIndex('lpd_status_index');
        });
    }
};
