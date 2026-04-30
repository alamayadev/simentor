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
        Schema::create('mitra_kepka', function (Blueprint $table) {
            $table->id();
            $table->text('email')->nullable();
            $table->text('sobat_id')->nullable();
            $table->text('posisi')->nullable();
            $table->text('status_seleksi')->nullable();
            $table->text('posisi_daftar')->nullable();
            $table->text('nama_lengkap')->nullable();
            $table->text('alamat_detail')->nullable();
            $table->text('alamat_prov')->nullable();
            $table->text('alamat_kab')->nullable();
            $table->text('alamat_kec')->nullable();
            $table->text('alamat_desa')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->text('jenis_kelamin')->nullable();
            $table->text('agama')->nullable();
            $table->text('status_kawin')->nullable();
            $table->text('pendidikan')->nullable();
            $table->text('pekerjaan')->nullable();
            $table->text('deskripsi_pekerjaan_lain')->nullable();
            $table->text('no_telp')->nullable();
            $table->text('npwp')->nullable();
            $table->text('kepemilikan_motor')->nullable();
            $table->text('kemampuan_berkendara_motor')->nullable();
            $table->text('pernah_capi')->nullable();
            $table->text('kepemilikan_hp_android')->nullable();
            $table->text('merk_hp')->nullable();
            $table->text('tipe_hp')->nullable();
            $table->integer('ram_hp')->nullable();
            $table->text('kepemilikan_laptop')->nullable();
            $table->text('kemampuan_komputer')->nullable();
            $table->text('mitra_eksternal')->nullable();
            $table->text('nama_k_l_lain')->nullable();
            $table->text('catatan')->nullable();
            $table->decimal('nilai_ujian')->nullable();
            $table->text('waktu_mulai')->nullable();
            $table->text('waktu_submit')->nullable();
            $table->text('durasi_menit')->nullable();
            $table->integer('remedial')->nullable();
            $table->text('kabid')->nullable();
            $table->text('kab')->nullable();
            $table->text('kecid')->nullable();
            $table->text('keca')->nullable();
            $table->text('desaid')->nullable();
            $table->text('desa')->nullable();
            $table->text('nik')->nullable();
            $table->text('foto')->nullable();
            $table->text('foto_ktp')->nullable();
            $table->text('ijazah')->nullable();
            $table->text('cek_kepka')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_kepka');
    }
};
