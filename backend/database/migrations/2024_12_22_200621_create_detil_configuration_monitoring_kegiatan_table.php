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
        // Check if table already exists
        if (!Schema::hasTable('detil_configuration_monitoring_kegiatan')) {
            Schema::create('detil_configuration_monitoring_kegiatan', function (Blueprint $table) {
                $table->unsignedBigInteger('detil_configuration_id');
                $table->unsignedBigInteger('monitoring_kegiatan_id');
                $table->primary(['detil_configuration_id', 'monitoring_kegiatan_id']);
                $table->timestamps();

                // Only add foreign keys if referenced tables exist
                if (Schema::hasTable('detil_configurations')) {
                    $table->foreign('detil_configuration_id', 'detil_config_fk')
                          ->references('id')
                          ->on('detil_configurations')
                          ->onDelete('cascade');
                }

                if (Schema::hasTable('monitoring_kegiatan')) {
                    $table->foreign('monitoring_kegiatan_id', 'monitoring_kegiatan_fk')
                          ->references('id')
                          ->on('monitoring_kegiatan')
                          ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detil_configuration_monitoring_kegiatan');
    }
};
