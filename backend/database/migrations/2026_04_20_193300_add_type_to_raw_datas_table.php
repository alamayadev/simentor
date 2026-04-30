<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_datas', function (Blueprint $table) {
            $table->string('type')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('raw_datas', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
