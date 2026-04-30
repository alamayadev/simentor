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
        Schema::create('alokasis', function (Blueprint $table) {
            $table->id();
            $table->string('idsls');
            $table->string('kdprov');
            $table->string('kdkab');
            $table->string('kdkec');
            $table->string('kddesa');
            $table->string('kdsls');
            $table->string('nmprov');
            $table->string('nmkab');
            $table->string('nmkec');
            $table->string('nmdesa');
            $table->string('nmsls');
            $table->string('periode');
            $table->string('idsubsls');
            $table->string('iddesa');
            $table->string('alokasi_scan')->nullable();
            $table->string('alokasi_georef')->nullable();
            $table->string('alokasi_geojson')->nullable();
            $table->string('alokasi_muatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alokasis');
    }
};
