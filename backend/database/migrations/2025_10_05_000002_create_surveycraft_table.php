<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveycraft', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('survey_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('json_file');
            $table->string('published_link')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->timestamp('timestamp')->useCurrent();

            $table->index('survey_id');
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveycraft');
    }
};
