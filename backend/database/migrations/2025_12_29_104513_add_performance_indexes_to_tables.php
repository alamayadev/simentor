<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PERFORMANCE FIX: Add indexes to frequently queried columns
     * to improve query performance and reduce database load.
     */
    public function up(): void
    {
        // Add indexes to users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Email is used for authentication and lookups
                if (!Schema::hasIndex('users', 'users_email_index')) {
                    $table->index('email', 'users_email_index');
                }

                // Name is used for searching and filtering
                if (!Schema::hasIndex('users', 'users_name_index')) {
                    $table->index('name', 'users_name_index');
                }

                // created_at is used for sorting and filtering
                if (!Schema::hasIndex('users', 'users_created_at_index')) {
                    $table->index('created_at', 'users_created_at_index');
                }
            });
        }

        // Add indexes to password_histories table
        if (Schema::hasTable('password_histories')) {
            Schema::table('password_histories', function (Blueprint $table) {
                // user_id is used for querying password history
                if (!Schema::hasIndex('password_histories', 'password_histories_user_id_index')) {
                    $table->index('user_id', 'password_histories_user_id_index');
                }

                // created_at is used for sorting to get recent passwords
                if (!Schema::hasIndex('password_histories', 'password_histories_created_at_index')) {
                    $table->index('created_at', 'password_histories_created_at_index');
                }
            });
        }

        // Add indexes to personal_access_tokens table
        if (Schema::hasTable('personal_access_tokens')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                // Improve token queries by user
                if (!Schema::hasIndex('personal_access_tokens', 'personal_access_tokens_tokenable_type_tokenable_id_index')) {
                    $table->index(['tokenable_type', 'tokenable_id'], 'personal_access_tokens_tokenable_type_tokenable_id_index');
                }

                // created_at is used for token expiration queries
                if (!Schema::hasIndex('personal_access_tokens', 'personal_access_tokens_created_at_index')) {
                    $table->index('created_at', 'personal_access_tokens_created_at_index');
                }
            });
        }

        // Add indexes to jobs table
        if (Schema::hasTable('jobs')) {
            Schema::table('jobs', function (Blueprint $table) {
                // Improve queue performance
                if (!Schema::hasIndex('jobs', 'jobs_queue_index')) {
                    $table->index('queue', 'jobs_queue_index');
                }

                // Improve job status queries
                if (!Schema::hasIndex('jobs', 'jobs_reserved_at_index')) {
                    $table->index('reserved_at', 'jobs_reserved_at_index');
                }
            });
        }

        // Add indexes to password_histories composite index for common queries
        if (Schema::hasTable('password_histories')) {
            Schema::table('password_histories', function (Blueprint $table) {
                // Composite index for user + created_at (common query pattern)
                if (!Schema::hasIndex('password_histories', 'password_histories_user_id_created_at_index')) {
                    $table->index(['user_id', 'created_at'], 'password_histories_user_id_created_at_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_email_index');
                $table->dropIndex('users_name_index');
                $table->dropIndex('users_created_at_index');
            });
        }

        // Drop indexes from password_histories table
        if (Schema::hasTable('password_histories')) {
            Schema::table('password_histories', function (Blueprint $table) {
                $table->dropIndex('password_histories_user_id_index');
                $table->dropIndex('password_histories_created_at_index');
                $table->dropIndex('password_histories_user_id_created_at_index');
            });
        }

        // Drop indexes from personal_access_tokens table
        if (Schema::hasTable('personal_access_tokens')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropIndex('personal_access_tokens_tokenable_type_tokenable_id_index');
                $table->dropIndex('personal_access_tokens_created_at_index');
            });
        }

        // Drop indexes from jobs table
        if (Schema::hasTable('jobs')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropIndex('jobs_queue_index');
                $table->dropIndex('jobs_reserved_at_index');
            });
        }
    }
};
