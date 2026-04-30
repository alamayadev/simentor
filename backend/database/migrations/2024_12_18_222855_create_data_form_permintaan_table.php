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
        Schema::create('data_form_permintaan', function (Blueprint $table) {
            $table->id();
            $table->text('thn')->nullable();
            $table->text('bulan')->nullable();
            $table->text('nomor')->nullable();
            $table->text('no_sisip')->nullable();
            $table->date('tanggal')->nullable();
            $table->text('tanggal_indo')->nullable();
            $table->text('kode_klas')->nullable();
            $table->text('no_surat')->nullable();
            $table->text('dari')->nullable();
            $table->text('perihal')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_form_permintaan');
    }
};
