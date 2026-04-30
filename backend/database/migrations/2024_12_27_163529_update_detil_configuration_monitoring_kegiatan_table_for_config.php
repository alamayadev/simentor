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
        // Create a temporary table with the new structure
        Schema::dropIfExists('detil_configuration_monitoring_kegiatan_temp');

        // Create temporary table with correct structure
        Schema::create('detil_configuration_monitoring_kegiatan_temp', function (Blueprint $table) {
            $table->unsignedBigInteger('detil_configuration_id');
            $table->unsignedBigInteger('monitoring_kegiatan_config_id');
            $table->primary(['detil_configuration_id', 'monitoring_kegiatan_config_id']);
            $table->timestamps();

            $table->foreign('detil_configuration_id', 'detil_config_fk_temp')
                  ->references('id')
                  ->on('detil_configurations')
                  ->onDelete('cascade');

            $table->foreign('monitoring_kegiatan_config_id', 'monitoring_kegiatan_config_fk_temp')
                  ->references('id')
                  ->on('monitoring_kegiatan_config')
                  ->onDelete('cascade');
        });

        // Copy data from old table to new table
        // We need to map the old monitoring_kegiatan_id to the corresponding monitoring_kegiatan_config_id
        // First, we'll need to get the old data and create mappings
        \DB::statement('INSERT INTO detil_configuration_monitoring_kegiatan_temp (detil_configuration_id, monitoring_kegiatan_config_id, created_at, updated_at)
            SELECT DISTINCT dcmk.detil_configuration_id, mk.monitoring_kegiatan_config_id, dcmk.created_at, dcmk.updated_at
            FROM detil_configuration_monitoring_kegiatan dcmk
            JOIN monitoring_kegiatan mk ON dcmk.monitoring_kegiatan_id = mk.id
            WHERE mk.monitoring_kegiatan_config_id IS NOT NULL');

        // Drop the old table
        Schema::dropIfExists('detil_configuration_monitoring_kegiatan');

        // Rename the temporary table to the original name
        Schema::rename('detil_configuration_monitoring_kegiatan_temp', 'detil_configuration_monitoring_kegiatan');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create a temporary table with the old structure
        Schema::dropIfExists('detil_configuration_monitoring_kegiatan_old');

        // Create temporary table with old structure
        Schema::create('detil_configuration_monitoring_kegiatan_old', function (Blueprint $table) {
            $table->unsignedBigInteger('detil_configuration_id');
            $table->unsignedBigInteger('monitoring_kegiatan_id');
            $table->primary(['detil_configuration_id', 'monitoring_kegiatan_id']);
            $table->timestamps();

            $table->foreign('detil_configuration_id', 'detil_config_fk_old')
                  ->references('id')
                  ->on('detil_configurations')
                  ->onDelete('cascade');

            $table->foreign('monitoring_kegiatan_id', 'monitoring_kegiatan_fk_old')
                  ->references('id')
                  ->on('monitoring_kegiatan')
                  ->onDelete('cascade');
        });

        // Copy data back from new table to old table
        // We need to map the monitoring_kegiatan_config_id back to monitoring_kegiatan_id
        \DB::statement('INSERT INTO detil_configuration_monitoring_kegiatan_old (detil_configuration_id, monitoring_kegiatan_id, created_at, updated_at)
            SELECT DISTINCT dcmk.detil_configuration_id, mk.id, dcmk.created_at, dcmk.updated_at
            FROM detil_configuration_monitoring_kegiatan dcmk
            JOIN monitoring_kegiatan mk ON dcmk.monitoring_kegiatan_config_id = mk.monitoring_kegiatan_config_id');

        // Drop the new table
        Schema::dropIfExists('detil_configuration_monitoring_kegiatan');

        // Rename the temporary table to the original name
        Schema::rename('detil_configuration_monitoring_kegiatan_old', 'detil_configuration_monitoring_kegiatan');
    }
};
