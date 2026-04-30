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
        Schema::create('landmark_tagging_wilkerstat', function (Blueprint $table) {
            $table->id();
            $table->string('wid')->nullable();
            $table->string('nama')->nullable();
            $table->string('nm_project')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('deskripsi_project')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->float('accuracy')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamp('user_created_at')->nullable();
            $table->string('kode_landmark_tipe')->nullable();
            $table->string('tipe_landmark')->nullable();
            $table->string('iddesa')->nullable();
            $table->string('user_creator_nama')->nullable();
            $table->text('photo_url')->nullable();
            $table->string('idsls')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landmark_tagging_wilkerstat');
    }
};
