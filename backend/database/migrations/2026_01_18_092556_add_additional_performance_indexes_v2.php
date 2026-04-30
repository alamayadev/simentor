<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kegiatan')) {
            Schema::table('kegiatan', function (Blueprint $table) {
                $table->string('tahun', 4)->nullable()->change();
                $table->string('fungsi', 100)->nullable()->change();
                $table->string('status', 50)->nullable()->change();
                $table->string('jenis_kegiatan', 100)->nullable()->change();

                if (!Schema::hasIndex('kegiatan', 'kegiatan_tahun_fungsi_index')) {
                    $table->index(['tahun', 'fungsi'], 'kegiatan_tahun_fungsi_index');
                }
                if (!Schema::hasIndex('kegiatan', 'kegiatan_status_jenis_index')) {
                    $table->index(['status', 'jenis_kegiatan'], 'kegiatan_status_jenis_index');
                }
            });
        }

        if (Schema::hasTable('mitra_kepka')) {
            Schema::table('mitra_kepka', function (Blueprint $table) {
                $table->string('nik', 16)->nullable()->change();
                $table->string('sobat_id', 50)->nullable()->change();
                $table->string('email', 100)->nullable()->change();
                $table->string('keca', 100)->nullable()->change();
                $table->string('desa', 100)->nullable()->change();

                if (!Schema::hasIndex('mitra_kepka', 'mitra_nik_index')) {
                    $table->index('nik', 'mitra_nik_index');
                }
                if (!Schema::hasIndex('mitra_kepka', 'mitra_sobat_id_index')) {
                    $table->index('sobat_id', 'mitra_sobat_id_index');
                }
                if (!Schema::hasIndex('mitra_kepka', 'mitra_email_index')) {
                    $table->index('email', 'mitra_email_index');
                }
                if (!Schema::hasIndex('mitra_kepka', 'mitra_keca_desa_index')) {
                    $table->index(['keca', 'desa'], 'mitra_keca_desa_index');
                }
            });
        }

        if (Schema::hasTable('alokasis')) {
            Schema::table('alokasis', function (Blueprint $table) {
                if (!Schema::hasIndex('alokasis', 'alokasis_nmkec_nmdesa_index')) {
                    $table->index(['nmkec', 'nmdesa'], 'alokasis_nmkec_nmdesa_index');
                }
                if (!Schema::hasIndex('alokasis', 'alokasis_kdkec_kddesa_index')) {
                    $table->index(['kdkec', 'kddesa'], 'alokasis_kdkec_kddesa_index');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kegiatan')) {
            Schema::table('kegiatan', function (Blueprint $table) {
                $table->dropIndex('kegiatan_tahun_fungsi_index');
                $table->dropIndex('kegiatan_status_jenis_index');
            });
        }

        if (Schema::hasTable('mitra_kepka')) {
            Schema::table('mitra_kepka', function (Blueprint $table) {
                $table->dropIndex('mitra_nik_index');
                $table->dropIndex('mitra_sobat_id_index');
                $table->dropIndex('mitra_email_index');
                $table->dropIndex('mitra_keca_desa_index');
            });
        }

        if (Schema::hasTable('alokasis')) {
            Schema::table('alokasis', function (Blueprint $table) {
                $table->dropIndex('alokasis_nmkec_nmdesa_index');
                $table->dropIndex('alokasis_kdkec_kddesa_index');
            });
        }
    }
};
