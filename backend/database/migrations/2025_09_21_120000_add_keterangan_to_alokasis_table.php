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
        Schema::table('alokasis', function (Blueprint $table) {
            // nullable keterangan column; default null is implicit for nullable columns
            $table->text('keterangan')->nullable()->after('alokasi_muatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alokasis', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
