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
        Schema::create('cek_scans', function (Blueprint $table) {
            $table->id();
            $table->string('kec')->nullable();
            $table->string('desa')->nullable();
            $table->string('filename')->nullable();
            $table->string('fullpath')->nullable();
            $table->dateTime('created_time')->nullable();
            $table->string('jenis')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('kodename')->nullable();
            $table->string('kode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cek_scans');
    }
};
