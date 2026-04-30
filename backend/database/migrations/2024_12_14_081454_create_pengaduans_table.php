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
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_pelangaran');
            $table->string('lainnya');
            $table->string('pelaku');
            $table->date('waktu_kejadian');
            $table->text('kronologi');
            $table->string('bukti');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
    }
};
