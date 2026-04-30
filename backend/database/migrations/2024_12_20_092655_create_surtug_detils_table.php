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
        Schema::create('data_surtug_detil', function (Blueprint $table) {
            $table->id();
            $table->integer('surtug_id');
            $table->unsignedBigInteger('pegawai_id')->nullable();
            $table->unsignedBigInteger('mitra_id')->nullable();
            $table->integer('grup_mitra')->nullable();
            $table->integer('grup_pegawai')->nullable();
            $table->unsignedBigInteger('penugasan_id')->nullable();
            $table->string('dasar');
            $table->string('nama_kegiatan');
            $table->string('tugas_sebagai')->nullable();
            $table->integer('hari');
            $table->string('wilayah_kerja');
            $table->date('tgl_mulai');
            $table->string('jenis_kendaraan')->nullable();
            $table->string('no_dipa');
            $table->boolean('isOrganik');
            $table->boolean('sppd')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_surtug_detil');
    }
};
