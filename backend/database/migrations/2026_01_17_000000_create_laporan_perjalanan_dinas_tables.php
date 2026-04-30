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
        Schema::create('laporan_perjalanan_dinas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_traveler');
            $table->string('tujuan');
            $table->string('lama_tanggal');
            $table->string('dalam_rangka');
            $table->string('pembebanan')->nullable();
            $table->foreignId('kode_keg')->nullable();
            $table->foreignId('user_id');
            $table->enum('status', ['draft', 'submitted', 'final'])->default('draft');
            $table->timestamps();
        });

        Schema::create('laporan_perjalanan_dinas_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_perjalanan_dinas_id');
            $table->date('tanggal');
            $table->text('uraian_lhp');
            $table->text('kendala')->nullable();
            $table->text('solusi')->nullable();
            $table->timestamps();

            $table->foreign('laporan_perjalanan_dinas_id', 'lpd_detail_fk')
                  ->references('id')
                  ->on('laporan_perjalanan_dinas')
                  ->onDelete('cascade');
        });

        Schema::create('laporan_perjalanan_dinas_dokumentasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_perjalanan_dinas_id');
            $table->string('file_path');
            $table->string('deskripsi')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->foreign('laporan_perjalanan_dinas_id', 'lpd_dok_fk')
                  ->references('id')
                  ->on('laporan_perjalanan_dinas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_perjalanan_dinas_detail', function (Blueprint $table) {
            $table->dropForeign(['laporan_perjalanan_dinas_id']);
        });

        Schema::table('laporan_perjalanan_dinas_dokumentasi', function (Blueprint $table) {
            $table->dropForeign(['laporan_perjalanan_dinas_id']);
        });

        Schema::dropIfExists('laporan_perjalanan_dinas_dokumentasi');
        Schema::dropIfExists('laporan_perjalanan_dinas_detail');
        Schema::dropIfExists('laporan_perjalanan_dinas');
    }
};
