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
        Schema::create('hardware', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('merk');
            $table->string('kategori');
            $table->string('spesifikasi');
            $table->string('status');
            $table->string('pemegang');
            $table->date('tgl_penyerahan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware');
    }
};