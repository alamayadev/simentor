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
        Schema::create('data_surtug', function (Blueprint $table) {
            $table->id();
            $table->text('bln')->nullable();
            $table->text('tahun')->nullable();
            $table->text('no_mix')->nullable();
            $table->text('nomor')->nullable();
            $table->text('no_sisip')->nullable();
            $table->date('tanggal')->nullable();
            $table->text('tanggal_indo')->nullable();
            $table->text('kode_klas')->nullable();
            $table->text('no_surat')->nullable();
            $table->text('kepada')->nullable();
            $table->text('menimbang')->nullable();
            $table->text('uraian')->nullable();
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
        Schema::dropIfExists('data_surtug');
    }
};
