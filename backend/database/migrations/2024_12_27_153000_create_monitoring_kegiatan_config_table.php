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
        Schema::create('monitoring_kegiatan_config', function (Blueprint $table) {
            $table->id();
            $table->string('fungsi')->nullable();
            $table->string('kegiatan_id')->nullable();
            $table->json('detil_configurations')->nullable(); // Store array of configuration IDs
            $table->timestamps();

            // Add unique constraint to prevent duplicate config for same fungsi/kegiatan combination
            $table->unique(['fungsi', 'kegiatan_id'], 'unique_fungsi_kegiatan_config');
        });

        // Add foreign key to monitoring_kegiatan table
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            $table->unsignedBigInteger('monitoring_kegiatan_config_id')->nullable()->after('desa_id');
            $table->foreign('monitoring_kegiatan_config_id')->references('id')->on('monitoring_kegiatan_config')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            // Check if column exists before trying to drop foreign key
            if (Schema::hasColumn('monitoring_kegiatan', 'monitoring_kegiatan_config_id')) {
                try {
                    $table->dropForeign(['monitoring_kegiatan_config_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->dropColumn('monitoring_kegiatan_config_id');
            }
        });

        Schema::dropIfExists('monitoring_kegiatan_config');
    }
};
