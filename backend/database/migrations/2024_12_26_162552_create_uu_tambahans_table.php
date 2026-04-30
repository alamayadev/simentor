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
        Schema::create('uu_tambahan', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_surat')->nullable(); // Tambahkan kolom jenis_surat
            $table->string('surat_id')->nullable();
            $table->string('item')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uu_tambahan');
    }
};
