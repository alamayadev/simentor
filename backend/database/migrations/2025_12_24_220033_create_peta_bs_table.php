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
        Schema::create('peta_bs', function (Blueprint $table) {
            $table->id();
            $table->integer('fid')->nullable();
            $table->string('kdprov')->nullable();
            $table->string('kdkab')->nullable();
            $table->string('kdkec')->nullable();
            $table->string('kddesa')->nullable();
            $table->string('nmdesa')->nullable();
            $table->string('nmkec')->nullable();
            $table->string('nmkab')->nullable();
            $table->string('nmprov')->nullable();
            $table->string('iddesa')->nullable();
            $table->string('idkec')->nullable();
            $table->string('sbs')->nullable();
            $table->string('base')->nullable();
            $table->string('idbs')->unique()->nullable();
            $table->string('kdbs')->nullable();
            $table->decimal('luas', 15, 7)->nullable();
            $table->text('kdsls')->nullable();
            $table->text('nmsls')->nullable();
            $table->string('posisi')->nullable();
            $table->text('dom_sls')->nullable();
            $table->string('dominan')->nullable();
            $table->string('tingkat')->nullable();
            $table->string('nm_gedung')->nullable();
            $table->string('kk')->nullable();
            $table->string('bstt')->nullable();
            $table->string('bstt_k')->nullable();
            $table->string('bsbtt')->nullable();
            $table->string('muatan')->nullable();
            $table->string('khusus')->nullable();
            $table->integer('count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peta_bs');
    }
};
