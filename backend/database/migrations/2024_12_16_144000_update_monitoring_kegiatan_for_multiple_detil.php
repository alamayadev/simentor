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
        // Drop the old detil_configuration_id column and detil JSON column
        if (Schema::hasColumn('monitoring_kegiatan', 'detil_configuration_id')) {
            // Try to drop foreign key if it exists (MySQL may silently fail if it doesn't exist)
            try {
                \DB::statement('ALTER TABLE monitoring_kegiatan DROP FOREIGN KEY IF EXISTS monitoring_kegiatan_detil_configuration_id_foreign');
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }

            Schema::table('monitoring_kegiatan', function (Blueprint $table) {
                $table->dropColumn(['detil_configuration_id', 'detil']);
            });
        }

        // Modify kegiatan_id column to ensure proper type and add foreign key if needed
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            // Ensure kegiatan_id is unsigned big integer
            $table->unsignedBigInteger('kegiatan_id')->change();
        });

        // Add new columns for multiple detil configurations
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            $table->json('detil_configurations')->nullable(); // Store array of configuration IDs
            $table->json('detil_data')->nullable(); // Store key-value pairs for detil data
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new columns
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            $table->dropColumn(['detil_configurations', 'detil_data']);
        });

        // Revert kegiatan_id column type if needed
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            // Revert kegiatan_id to string if it was changed
            try {
                $table->string('kegiatan_id')->change();
            } catch (\Exception $e) {
                // Column type change failed, continue
            }
        });

        // Add back the old columns
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            $table->unsignedBigInteger('detil_configuration_id')->nullable();
            $table->json('detil')->nullable();

            // Foreign key constraint
            $table->foreign('detil_configuration_id')
                  ->references('id')
                  ->on('detil_configurations')
                  ->onDelete('set null');
        });
    }
};
