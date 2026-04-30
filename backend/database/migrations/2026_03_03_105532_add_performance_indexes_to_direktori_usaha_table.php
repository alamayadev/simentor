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
        Schema::table('direktori_usaha', function (Blueprint $table) {
            $table->index('nama_usaha');
            $table->index('gcs_result');
            $table->index('gc_username');
            $table->index('latlong_status_gc');
            $table->index('name_similarity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direktori_usaha', function (Blueprint $table) {
            $table->dropIndex(['nama_usaha']);
            $table->dropIndex(['gcs_result']);
            $table->dropIndex(['gc_username']);
            $table->dropIndex(['latlong_status_gc']);
            $table->dropIndex(['name_similarity']);
        });
    }
};
