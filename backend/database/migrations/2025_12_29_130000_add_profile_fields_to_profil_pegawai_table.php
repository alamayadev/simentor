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
        Schema::table('profil_pegawai', function (Blueprint $table) {
            $table->string('gelar_depan')->nullable()->after('nip');
            $table->string('gelar_belakang')->nullable()->after('gelar_depan');
            $table->string('tempat_lahir')->nullable()->after('gelar_belakang');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->text('alamat')->nullable()->after('tanggal_lahir');
            $table->string('no_hp')->nullable()->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profil_pegawai', function (Blueprint $table) {
            $table->dropColumn([
                'gelar_depan',
                'gelar_belakang',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat',
                'no_hp',
            ]);
        });
    }
};
