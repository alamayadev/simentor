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
        Schema::create('profil_pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('pangkat');
            $table->string('gol');
            $table->string('nip');
            $table->string('jabatan');
            $table->integer('kelas');
            $table->integer('user_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_pegawai');
    }
};
