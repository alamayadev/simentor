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
        Schema::create('sls_2024', function (Blueprint $table) {
            $table->id();
            $table->string('kdprov', 2);
            $table->string('kdkab', 2);
            $table->string('kdkec', 10);
            $table->string('kddesa', 10);
            $table->string('kdsls', 10);
            $table->string('idsls', 20);
            $table->string('nmprov', 10);
            $table->string('nmkab', 10);
            $table->string('nmkec', 20);
            $table->string('nmdesa', 30);
            $table->string('nmsls', 100);
            $table->string('periode', 10);
            $table->smallInteger('pcl_id')->nullable();
            $table->smallInteger('pml_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sls_2024');
    }
};
