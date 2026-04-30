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
        Schema::table('data_surat_keluar', function (Blueprint $table) {
            if (!Schema::hasColumn('data_surat_keluar', 'tembusan')) {
                $table->json('tembusan')->nullable()->after('isi_surat');
            }

            if (!Schema::hasColumn('data_surat_keluar', 'sifat')) {
                $table->string('sifat')->nullable()->after('tembusan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_surat_keluar', function (Blueprint $table) {
            if (Schema::hasColumn('data_surat_keluar', 'tembusan')) {
                $table->dropColumn('tembusan');
            }

            if (Schema::hasColumn('data_surat_keluar', 'sifat')) {
                $table->dropColumn('sifat');
            }
        });
    }
};
