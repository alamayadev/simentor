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
        Schema::create('sls_2025', function (Blueprint $table) {
            $table->id();
            $table->string('kecamatan_id');
            $table->foreign('kecamatan_id')->references('id')->on('kecamatans')->onDelete('cascade');
            $table->string('desa_id');
            $table->foreign('desa_id')->references('id')->on('desas')->onDelete('cascade');
            $table->string('kdkec', 3);
            $table->string('kddesa', 3);
            $table->string('kdsls', 4);
            $table->string('nama_sls');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sls_2025');
    }
};
