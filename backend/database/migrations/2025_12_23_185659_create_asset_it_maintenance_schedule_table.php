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
        Schema::create('asset_it_maintenance_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id'); // references id in asset_it table
            $table->date('next_maintenance');
            $table->string('responsible_team');
            $table->timestamps();

            $table->foreign('asset_id')
                  ->references('id')
                  ->on('asset_it')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_it_maintenance_schedule');
    }
};
