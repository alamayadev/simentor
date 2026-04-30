<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('budget_sakti_realizations')) {
            return;
        }

        if (! Schema::hasColumn('budget_sakti_realizations', 'usage_date')) {
            Schema::table('budget_sakti_realizations', function (Blueprint $table) {
                $table->date('usage_date')->nullable()->after('tahun_anggaran')->index();
            });

            DB::table('budget_sakti_realizations')
                ->whereNull('usage_date')
                ->update(['usage_date' => DB::raw('DATE(imported_at)')]);

            Schema::table('budget_sakti_realizations', function (Blueprint $table) {
                $table->date('usage_date')->nullable(false)->change();
            });
        }

        Schema::table('budget_sakti_realizations', function (Blueprint $table) {
            $table->dropUnique('budget_sakti_realizations_unique');
            $table->unique(['tahun_anggaran', 'usage_date', 'composite_key'], 'budget_sakti_realizations_unique');
        });

        DB::statement('DROP VIEW IF EXISTS v_sakti_reconciliation');
        DB::statement("
            CREATE VIEW v_sakti_reconciliation AS
            SELECT
                bi.composite_key AS budget_item_key,
                bi.description AS uraian_kegiatan,
                bi.tahun_anggaran,
                bi.program_code,
                bi.activity_code,
                bi.output_code,
                bi.component_code,
                bi.sub_component_code,
                bi.account_code,
                COALESCE(bu.total_spent, 0) AS total_internal,
                COALESCE(sr.total_sakti, 0) AS total_sakti,
                (COALESCE(bu.total_spent, 0) - COALESCE(sr.total_sakti, 0)) AS selisih
            FROM (
                SELECT DISTINCT
                    composite_key,
                    description,
                    tahun_anggaran,
                    program_code,
                    activity_code,
                    output_code,
                    component_code,
                    sub_component_code,
                    account_code
                FROM budget_items
            ) bi
            LEFT JOIN (
                SELECT
                    budget_item_key,
                    SUM(amount_spent) AS total_spent
                FROM budget_usage
                GROUP BY budget_item_key
            ) bu ON bi.composite_key = bu.budget_item_key
            LEFT JOIN (
                SELECT
                    tahun_anggaran,
                    composite_key,
                    SUM(sd_periode) AS total_sakti
                FROM budget_sakti_realizations
                GROUP BY tahun_anggaran, composite_key
            ) sr ON bi.composite_key = sr.composite_key
                AND bi.tahun_anggaran = sr.tahun_anggaran
            WHERE sr.composite_key IS NOT NULL OR bu.total_spent IS NOT NULL
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('budget_sakti_realizations')) {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS v_sakti_reconciliation');

        Schema::table('budget_sakti_realizations', function (Blueprint $table) {
            $table->dropUnique('budget_sakti_realizations_unique');
            $table->unique(['tahun_anggaran', 'composite_key'], 'budget_sakti_realizations_unique');
        });

        if (Schema::hasColumn('budget_sakti_realizations', 'usage_date')) {
            Schema::table('budget_sakti_realizations', function (Blueprint $table) {
                $table->dropColumn('usage_date');
            });
        }

        DB::statement("
            CREATE VIEW v_sakti_reconciliation AS
            SELECT
                bi.composite_key AS budget_item_key,
                bi.description AS uraian_kegiatan,
                bi.tahun_anggaran,
                bi.program_code,
                bi.activity_code,
                bi.output_code,
                bi.component_code,
                bi.sub_component_code,
                bi.account_code,
                COALESCE(bu.total_spent, 0) AS total_internal,
                COALESCE(sr.sd_periode, 0) AS total_sakti,
                (COALESCE(bu.total_spent, 0) - COALESCE(sr.sd_periode, 0)) AS selisih
            FROM (
                SELECT DISTINCT
                    composite_key,
                    description,
                    tahun_anggaran,
                    program_code,
                    activity_code,
                    output_code,
                    component_code,
                    sub_component_code,
                    account_code
                FROM budget_items
            ) bi
            LEFT JOIN (
                SELECT
                    budget_item_key,
                    SUM(amount_spent) AS total_spent
                FROM budget_usage
                GROUP BY budget_item_key
            ) bu ON bi.composite_key = bu.budget_item_key
            LEFT JOIN budget_sakti_realizations sr ON bi.composite_key = sr.composite_key
                AND bi.tahun_anggaran = sr.tahun_anggaran
            WHERE sr.id IS NOT NULL OR bu.total_spent IS NOT NULL
        ");
    }
};
