<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PERFORMANCE FIX: Add indexes to optimize search queries
     *
     * This migration adds indexes to the users table to improve query performance
     * for searches on email and name columns, which are frequently used in:
     * - SkpApiController: User::where('email', 'like', "%@bps.go.id")
     * - SkpApiController: User::where('email', 'like', "%@bps.go.id")->whereNotIn()
     * - SkpApiController: User::where('id', '!=', 1)->orderBy('name', 'ASC')
     *
     * Indexes significantly improve query performance when searching or filtering
     * by these columns, especially as the user base grows.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // PERFORMANCE FIX: Add index on email column
            // Used in SkpApiController for filtering BPS employees
            if (!Schema::hasIndex('users', 'users_email_index')) {
                $table->index('email', 'users_email_index');
            }

            // PERFORMANCE FIX: Add index on name column
            // Used in SkpApiController for sorting and searching users
            if (!Schema::hasIndex('users', 'users_name_index')) {
                $table->index('name', 'users_name_index');
            }

            // PERFORMANCE FIX: Add index on created_at column
            // Useful for ordering by creation date and recent user queries
            if (!Schema::hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at', 'users_created_at_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_index');
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_created_at_index');
        });
    }
};
