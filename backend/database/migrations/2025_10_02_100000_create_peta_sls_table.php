<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peta_sls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kdkec', 4)->nullable();
            $table->string('kddesa', 4)->nullable();
            $table->string('nmkec')->nullable();
            $table->string('nmdesa')->nullable();
            $table->string('filename')->nullable();
            $table->string('operator')->nullable();
            $table->unsignedSmallInteger('jml')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['kdkec', 'kddesa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peta_sls');
    }
};