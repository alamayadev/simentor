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
        if (! Schema::hasTable('direktori_usaha')) {
            Schema::create('direktori_usaha', function (Blueprint $table) {
                $table->id();
                $table->string('idsbr')->nullable()->index();
                $table->string('nama_usaha')->nullable();
                $table->text('alamat_usaha')->nullable();
                $table->string('kode_wilayah')->nullable();
                $table->string('kdprov', 2)->nullable();
                $table->string('kdkab', 2)->nullable();
                $table->string('kdkec', 10)->nullable();
                $table->string('kddesa', 10)->nullable();
                $table->string('nmprov')->nullable();
                $table->string('nmkab')->nullable();
                $table->string('nmkec')->nullable();
                $table->string('nmdesa')->nullable();
                $table->text('perusahaan_id')->nullable();
                $table->string('status_perusahaan')->nullable();
                $table->string('skor_kalo')->nullable();
                $table->string('kegiatan_usaha')->nullable();
                $table->string('rank_nama')->nullable();
                $table->string('rank_alamat')->nullable();
                $table->date('history_ref_profiling_id')->nullable();
                $table->string('skala_usaha')->nullable();
                $table->string('sumber_data')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('latlong_status')->nullable();
                $table->string('hasilgc')->nullable();
                $table->text('gcid')->nullable();
                $table->string('gcs_result')->nullable();
                $table->boolean('allow_cancel')->default(false);
                $table->boolean('allow_edit')->default(false);
                $table->boolean('allow_flagging')->default(false);
                $table->string('latitude_gc')->nullable();
                $table->string('longitude_gc')->nullable();
                $table->string('latlong_status_gc')->nullable();
                $table->string('gc_username')->nullable();
                $table->string('nama_usaha_gc')->nullable();
                $table->string('alamat_usaha_gc')->nullable();
                $table->decimal('name_similarity', 5, 2)->nullable();
                $table->unsignedBigInteger('update_by')->nullable();
                $table->timestamps();

                // Indexes for common queries
                $table->index(['kdprov', 'kdkab', 'kdkec', 'kddesa']);
                $table->index('status_perusahaan');
                $table->index('latlong_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direktori_usaha');
    }
};
