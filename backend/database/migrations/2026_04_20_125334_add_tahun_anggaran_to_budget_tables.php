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
        if (Schema::hasTable('budget_items')) {
            Schema::table('budget_items', function (Blueprint $table) {
                $table->integer('tahun_anggaran')->nullable()->after('revision_date');
            });
        }

        if (Schema::hasTable('imported_files')) {
            Schema::table('imported_files', function (Blueprint $table) {
                $table->integer('tahun_anggaran')->nullable()->after('revision_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('budget_items')) {
            Schema::table('budget_items', function (Blueprint $table) {
                $table->dropColumn('tahun_anggaran');
            });
        }

        if (Schema::hasTable('imported_files')) {
            Schema::table('imported_files', function (Blueprint $table) {
                $table->dropColumn('tahun_anggaran');
            });
        }
    }
};
