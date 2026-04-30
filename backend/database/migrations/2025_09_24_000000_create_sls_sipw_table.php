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
        Schema::create('sls_sipw', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('idfrs')->nullable();
            $table->string('idsls')->nullable()->index();
            // Use string types for administrative codes because some values may contain leading zeros
            $table->string('kdprov')->nullable();
            $table->string('kdkab')->nullable();
            $table->string('kdkec')->nullable();
            $table->string('kddesa')->nullable();
            $table->string('kdsls')->nullable();
            $table->unsignedTinyInteger('klas')->nullable();
            $table->string('nmprov')->nullable();
            $table->string('nmkab')->nullable();
            $table->string('nmkec')->nullable();
            $table->string('nmdesa')->nullable();
            $table->string('nama_sls')->nullable();
            $table->string('jenis_sls')->nullable();
            $table->string('ketua_sls')->nullable();
            $table->unsignedSmallInteger('j_subsls')->nullable();
            $table->string('muatan_dominan')->nullable();
            $table->tinyInteger('flag_perubahan_sls')->nullable();
            $table->string('status_olah_peta')->nullable();
            $table->string('shapes_comparation')->nullable();
            $table->string('location')->nullable();
            $table->string('status_sls')->nullable();
            $table->string('peta_banding_rs')->nullable();
            $table->string('operator')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sls_sipw');
    }
};
