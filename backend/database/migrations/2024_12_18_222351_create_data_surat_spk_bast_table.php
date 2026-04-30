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
        Schema::create('data_surat_sk_bast', function (Blueprint $table) {
            $table->id();
            $table->text('bln')->nullable();
            $table->text('thn')->nullable();
            $table->text('nomor')->nullable();
            $table->text('no_sisip')->nullable();
            $table->date('tanggal')->nullable();
            $table->text('kode_klas')->nullable();
            $table->text('no_surat')->nullable();
            $table->text('oleh')->nullable();
            $table->text('kegiatan')->nullable();
            $table->text('kepada')->nullable();
            $table->text('perihal')->nullable();
            $table->text('type')->nullable();
            $table->text('kol_lampiran')->nullable();
            $table->integer('create_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_surat_sk_bast');
    }
};
