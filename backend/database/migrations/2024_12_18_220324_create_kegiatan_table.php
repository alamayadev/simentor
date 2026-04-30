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
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->text('tahun')->nullable();
            $table->text('fungsi')->nullable();
            $table->text('kode_kelompok_kegiatan')->nullable();
            $table->text('kode_kegiatan')->nullable();
            $table->text('nama')->nullable();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->text('jenis_kegiatan')->nullable();
            $table->integer('jml_ptgs')->nullable();
            $table->integer('volume')->nullable();
            $table->text('satuan')->nullable();
            $table->integer('rate_pcl')->nullable();
            $table->integer('rate_pml')->nullable();
            $table->integer('rate_entri')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
