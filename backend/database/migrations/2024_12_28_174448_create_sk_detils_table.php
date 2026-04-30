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
        Schema::create('sk_detils', function (Blueprint $table) {
            $table->id();
            $table->integer('sk_id');
            $table->unsignedBigInteger('pegawai_id')->nullable();
            $table->unsignedBigInteger('mitra_id')->nullable();
            $table->unsignedBigInteger('penugasan_id')->nullable();
            $table->string('detil')->nullable();
            $table->integer('isOrganik')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sk_detils');
    }
};
