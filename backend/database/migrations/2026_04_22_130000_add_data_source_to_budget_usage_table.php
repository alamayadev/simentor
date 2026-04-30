<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('budget_usage')) {
            return;
        }

        if (! Schema::hasColumn('budget_usage', 'data_source')) {
            Schema::table('budget_usage', function (Blueprint $table) {
                $table->string('data_source', 16)->default('sakti')->after('amount_spent');
            });
        }

        DB::table('budget_usage')
            ->whereNull('data_source')
            ->update(['data_source' => 'sakti']);

        DB::statement('DROP VIEW IF EXISTS v_usage_history');
        DB::statement("
            CREATE VIEW v_usage_history AS
            SELECT
                u.id,
                u.usage_date,
                u.description as usage_description,
                u.amount_spent,
                u.data_source,
                b.revision_name as revision_at_time,
                b.description as budget_item_at_time,
                b.total_amount as pagu_at_time,
                u.budget_item_key
            FROM budget_usage u
            LEFT JOIN budget_items b ON u.budget_item_key = b.composite_key AND u.usage_date >= b.revision_date
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('budget_usage')) {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS v_usage_history');
        DB::statement("
            CREATE VIEW v_usage_history AS
            SELECT
                u.id,
                u.usage_date,
                u.description as usage_description,
                u.amount_spent,
                b.revision_name as revision_at_time,
                b.description as budget_item_at_time,
                b.total_amount as pagu_at_time,
                u.budget_item_key
            FROM budget_usage u
            LEFT JOIN budget_items b ON u.budget_item_key = b.composite_key AND u.usage_date >= b.revision_date
        ");

        if (Schema::hasColumn('budget_usage', 'data_source')) {
            Schema::table('budget_usage', function (Blueprint $table) {
                $table->dropColumn('data_source');
            });
        }
    }
};
