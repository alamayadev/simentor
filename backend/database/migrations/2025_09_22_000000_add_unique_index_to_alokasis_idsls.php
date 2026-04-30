<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasColumn('alokasis', 'idsls')) {
            // nothing to do if column missing
            return;
        }

        Schema::table('alokasis', function (Blueprint $table) {
            // Create unique index on idsls. We expect no duplicates (verified before running).
            $table->unique('idsls');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (! Schema::hasColumn('alokasis', 'idsls')) {
            return;
        }

        Schema::table('alokasis', function (Blueprint $table) {
            // Drop the unique index on idsls
            $table->dropUnique(['idsls']);
        });
    }
};
