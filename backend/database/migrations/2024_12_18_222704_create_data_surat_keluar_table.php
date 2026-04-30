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
        Schema::create('data_surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->text('bln')->nullable();
            $table->text('thn')->nullable();
            $table->text('nomor')->nullable();
            $table->text('no_sisip')->nullable();
            $table->date('tanggal')->nullable();
            $table->text('tanggal_indo')->nullable();
            $table->text('no_surat')->nullable();
            $table->text('dari')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('perihal')->nullable();
            $table->text('isi_surat')->nullable();
            $table->json('tembusan')->nullable();
            $table->string('sifat')->nullable();
            $table->integer('lampiran')->nullable();
            $table->text('file')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_surat_keluar');
    }
};
