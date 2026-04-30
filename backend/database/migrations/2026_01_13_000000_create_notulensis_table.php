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
        Schema::dropIfExists('notulensis');
        Schema::create('notulensis', function (Blueprint $column) {
            $column->id();
            $column->string('judul');
            $column->string('instansi')->nullable();
            $column->string('kegiatan')->nullable();
            $column->string('topik')->nullable();
            $column->date('tanggal_rapat')->nullable();
            $column->time('waktu_mulai')->nullable();
            $column->time('waktu_selesai')->nullable();
            $column->string('tempat')->nullable();
            $column->string('jabatan_pimpinan_rapat')->nullable();
            
            $column->unsignedBigInteger('pimpinan_id')->nullable();
            $column->unsignedBigInteger('notulis_id')->nullable();
            $column->string('nip_pimpinan')->nullable();
            $column->string('nip_notulis')->nullable();
            
            $column->json('peserta')->nullable();
            $column->longText('agenda')->nullable();
            $column->longText('resume')->nullable();
            $column->longText('tanya_jawab')->nullable();
            $column->integer('kategori')->default(1);
            
            $column->timestamps();

            // Foreign keys to pegawais table if it exists
            $column->foreign('pimpinan_id')->references('id')->on('profil_pegawai')->onDelete('set null');
            $column->foreign('notulis_id')->references('id')->on('profil_pegawai')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notulensis');
    }
};
