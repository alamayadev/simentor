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
        Schema::table('sls_kecs', function (Blueprint $table) {
            // Columns observed in initial_data/sls_kec.json but not present in migration
            if (!Schema::hasColumn('sls_kecs', 'pemeta')) {
                $table->string('pemeta')->nullable();
            }
            if (!Schema::hasColumn('sls_kecs', 'pengawas')) {
                $table->string('pengawas')->nullable();
            }
            if (!Schema::hasColumn('sls_kecs', 'pengawas_organik')) {
                $table->string('pengawas_organik')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sls_kecs', function (Blueprint $table) {
            if (Schema::hasColumn('sls_kecs', 'pemeta')) {
                $table->dropColumn('pemeta');
            }
            if (Schema::hasColumn('sls_kecs', 'pengawas')) {
                $table->dropColumn('pengawas');
            }
            if (Schema::hasColumn('sls_kecs', 'pengawas_organik')) {
                $table->dropColumn('pengawas_organik');
            }
        });
    }
};
