<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->dropSqliteBlockingView();

        $this->renameTable('data_form_permintaan', 'surat_permintaan');
        $this->renameTable('data_surat_keluar', 'surat_keluar');
        $this->renameTable('data_surat_sk_bast', 'surat_sk_bast');
        $this->renameTable('data_surtug', 'surat_tugas');
        $this->renameTable('data_surtug_detil', 'surat_tugas_detil');
        $this->renameTable('bast_detils', 'surat_bast_detil');
        $this->renameTable('sk_detils', 'surat_sk_detil');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->dropSqliteBlockingView();

        $this->renameTable('surat_permintaan', 'data_form_permintaan');
        $this->renameTable('surat_keluar', 'data_surat_keluar');
        $this->renameTable('surat_sk_bast', 'data_surat_sk_bast');
        $this->renameTable('surat_tugas', 'data_surtug');
        $this->renameTable('surat_tugas_detil', 'data_surtug_detil');
        $this->renameTable('surat_bast_detil', 'bast_detils');
        $this->renameTable('surat_sk_detil', 'sk_detils');

        Schema::enableForeignKeyConstraints();
    }

    private function renameTable(string $from, string $to): void
    {
        if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }

    private function dropSqliteBlockingView(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP VIEW IF EXISTS v_sakti_reconciliation');
        }
    }
};
