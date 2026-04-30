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
        // Try to drop foreign key if it exists (MySQL may silently fail if it doesn't exist)
        try {
            \DB::statement('ALTER TABLE monitoring_kegiatan DROP FOREIGN KEY IF EXISTS monitoring_kegiatan_kegiatan_id_foreign');
        } catch (\Exception $e) {
            // Foreign key doesn't exist, continue
        }

        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            // Change kegiatan_id from string to unsigned big integer
            $table->unsignedBigInteger('kegiatan_id')->change();

            // Add foreign key constraint to kegiatan table if it exists
            if (Schema::hasTable('kegiatan')) {
                $table->foreign('kegiatan_id')
                      ->references('id')
                      ->on('kegiatan')
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['kegiatan_id']);
        });

        Schema::table('monitoring_kegiatan', function (Blueprint $table) {
            // Change kegiatan_id back to string
            $table->string('kegiatan_id')->change();
        });
    }
};
