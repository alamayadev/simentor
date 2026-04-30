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
        Schema::create('penugasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kegiatan_id');
            $table->string('jabatan_tugas')->nullable();
            $table->unsignedBigInteger('pegawai_id')->nullable();
            $table->unsignedBigInteger('mitra_id')->nullable();
            $table->integer('volume');
            $table->integer('nilai');
            $table->date('bln_bayar')->nullable();
            $table->integer('created_by');
            $table->string('no_bast')->nullable();
            $table->date('tgl_bast')->nullable();
            $table->string('no_sk')->nullable();
            $table->date('tgl_sk')->nullable();
            $table->date('jangka_waktu_mulai')->nullable();
            $table->date('jangka_waktu_selesai')->nullable();
            $table->integer('nilai_pulsa')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penugasan');
    }
};
