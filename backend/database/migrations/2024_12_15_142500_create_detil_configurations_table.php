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
        Schema::create('detil_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('related_table')->nullable(); // e.g., 'penugasan' or null for custom
            $table->string('foreign_key')->nullable(); // e.g., 'kegiatan_id' or null for custom
            $table->json('field'); // Single field definition
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detil_configurations');
    }
};
