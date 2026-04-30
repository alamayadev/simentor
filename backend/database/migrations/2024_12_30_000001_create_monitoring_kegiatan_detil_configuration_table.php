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
        Schema::create('monitoring_kegiatan_detil_configuration', function (Blueprint $table) {
            $table->unsignedBigInteger('monitoring_kegiatan_id');
            $table->unsignedBigInteger('detil_configuration_id');
            $table->primary(['monitoring_kegiatan_id', 'detil_configuration_id']);
            $table->timestamps();

            $table->foreign('monitoring_kegiatan_id', 'mk_detil_config_mk_id_foreign')
                  ->references('id')
                  ->on('monitoring_kegiatan')
                  ->onDelete('cascade');

            $table->foreign('detil_configuration_id', 'mk_detil_config_dc_id_foreign')
                  ->references('id')
                  ->on('detil_configurations')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_kegiatan_detil_configuration');
    }
};