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
        Schema::create('asset_it', function (Blueprint $table) {
            $table->id();
            $table->string('kode_asset')->unique();
            $table->enum('type', ['hardware', 'software', 'network']);
            $table->string('category');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('name')->nullable(); // for software
            $table->string('license_key')->nullable(); // for software
            $table->string('device')->nullable(); // for network devices
            $table->string('ip_address')->nullable(); // for network devices
            $table->string('location')->nullable();
            $table->enum('status', ['Baik','Rusak Ringan','Rusak Berat','Dalam Perbaikan']);
            $table->string('assigned_to')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('expiry_date')->nullable(); // for software subscriptions
            $table->date('delivery_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_it');
    }
};
