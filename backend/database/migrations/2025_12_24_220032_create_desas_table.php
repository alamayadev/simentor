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
        Schema::create('desas', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('kecamatan_id');
            $table->foreign('kecamatan_id')->references('id')->on('kecamatans')->onDelete('cascade');
            $table->string('kdkec', 3);
            $table->string('kddesa', 3);
            $table->string('nmdesa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desas');
    }
};
