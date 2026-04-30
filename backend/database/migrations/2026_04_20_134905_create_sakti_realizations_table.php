<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_sakti_realizations', function (Blueprint $table) {
            $table->id();
            $table->year('tahun_anggaran')->index();
            $table->date('usage_date')->index();
            
            // Mirror budget_items schema
            $table->string('program_code', 16)->nullable();
            $table->string('program_name', 512)->nullable();
            $table->string('activity_code', 16)->nullable();
            $table->string('activity_name', 512)->nullable();
            $table->string('output_code', 32)->nullable();
            $table->string('output_name', 512)->nullable();
            $table->string('component_code', 16)->nullable();
            $table->string('component_name', 512)->nullable();
            $table->string('sub_component_code', 16)->nullable();
            $table->string('sub_component_name', 512)->nullable();
            $table->string('account_code', 16)->nullable();
            $table->string('account_name', 512)->nullable();
            
            // Item details
            $table->text('description')->nullable();
            $table->text('original_description')->nullable();
            
            // SAKTI-specific values
            $table->decimal('periode_lalu', 20, 2)->default(0);
            $table->decimal('periode_ini', 20, 2)->default(0);
            $table->decimal('sd_periode', 20, 2)->default(0);
            $table->decimal('total_amount', 20, 2)->default(0); // Matches sd_periode
            
            $table->string('composite_key', 64)->index();
            $table->timestamp('imported_at')->useCurrent();
            
            $table->unique(['tahun_anggaran', 'usage_date', 'composite_key'], 'budget_sakti_realizations_unique');
        });

        // Create the Item-level reconciliation view only if dependent tables exist
        if (Schema::hasTable('budget_items') && Schema::hasTable('budget_usage')) {
            $viewSql = "
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
                    /* Now we use the pre-calculated composite_key which is already harmonized/normalized */
                    SELECT DISTINCT
                        composite_key,
                        description,
                        tahun_anggaran, program_code, activity_code, output_code, component_code, sub_component_code, account_code
                    FROM budget_items
                ) bi
                LEFT JOIN (
                    /* Group usage by the budget_item_key directly */
                    SELECT 
                        budget_item_key,
                        SUM(amount_spent) AS total_spent 
                    FROM budget_usage bus
                    GROUP BY budget_item_key
                ) bu ON bi.composite_key = bu.budget_item_key
                LEFT JOIN (
                    SELECT
                        tahun_anggaran,
                        composite_key,
                        SUM(sd_periode) AS sd_periode
                    FROM budget_sakti_realizations
                    GROUP BY tahun_anggaran, composite_key
                ) sr ON bi.composite_key = sr.composite_key 
                    AND bi.tahun_anggaran = sr.tahun_anggaran
                WHERE sr.id IS NOT NULL OR bu.total_spent IS NOT NULL
            ";

            if (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP VIEW IF EXISTS v_sakti_reconciliation');
                DB::statement($viewSql);
            } else {
                DB::statement(str_replace('CREATE VIEW', 'CREATE OR REPLACE VIEW', $viewSql));
            }
        }
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_sakti_reconciliation");
        Schema::dropIfExists('budget_sakti_realizations');
    }
};
