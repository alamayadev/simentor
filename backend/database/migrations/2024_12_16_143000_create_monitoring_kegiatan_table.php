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
        if (!Schema::hasTable('monitoring_kegiatan')) {
            Schema::create('monitoring_kegiatan', function (Blueprint $table) {
                $table->id();
                $table->string('fungsi');
                $table->unsignedBigInteger('kegiatan_id');
                $table->string('kec_id');
                $table->string('desa_id');
                $table->string('kode_sampel');
                $table->unsignedBigInteger('detil_configuration_id')->nullable();
                $table->json('detil')->nullable();
                $table->timestamps();

                // Foreign keys will be added separately to avoid migration order issues
            });
        } else {
            // Table exists, ensure column types and foreign keys are correct
            Schema::table('monitoring_kegiatan', function (Blueprint $table) {
                // Ensure kegiatan_id is unsigned big integer
                try {
                    $table->unsignedBigInteger('kegiatan_id')->change();
                } catch (\Exception $e) {
                    // Column type is already correct, continue
                }

                // Try to add foreign key constraint if it doesn't exist AND referenced table exists
                if (Schema::hasTable('kegiatan')) {
                    try {
                        $table->foreign('kegiatan_id')
                              ->references('id')
                              ->on('kegiatan')
                              ->onDelete('cascade');
                    } catch (\Exception $e) {
                        // Foreign key already exists, continue
                    }
                }

                // Try to add foreign key constraint for detil_configuration_id if it doesn't exist AND referenced table exists
                if (Schema::hasTable('detil_configurations')) {
                    try {
                        $table->foreign('detil_configuration_id')
                              ->references('id')
                              ->on('detil_configurations')
                              ->onDelete('set null');
                    } catch (\Exception $e) {
                        // Foreign key already exists, continue
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_kegiatan');
    }
};
