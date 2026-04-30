<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseIndexesTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_email_index()
    {
        // PERFORMANCE FIX: Verify email index exists for authentication queries
        $this->assertTrue(Schema::hasIndex('users', 'users_email_index'));
    }

    public function test_users_table_has_name_index()
    {
        // PERFORMANCE FIX: Verify name index exists for search queries
        $this->assertTrue(Schema::hasIndex('users', 'users_name_index'));
    }

    public function test_users_table_has_created_at_index()
    {
        // PERFORMANCE FIX: Verify created_at index exists for sorting/filtering
        $this->assertTrue(Schema::hasIndex('users', 'users_created_at_index'));
    }

    public function test_password_histories_table_has_user_id_index()
    {
        // PERFORMANCE FIX: Verify user_id index exists for password history queries
        $this->assertTrue(Schema::hasIndex('password_histories', 'password_histories_user_id_index'));
    }

    public function test_password_histories_table_has_created_at_index()
    {
        // PERFORMANCE FIX: Verify created_at index exists for sorting passwords
        $this->assertTrue(Schema::hasIndex('password_histories', 'password_histories_created_at_index'));
    }

    public function test_password_histories_table_has_composite_index()
    {
        // PERFORMANCE FIX: Verify composite index for user + created_at queries
        $this->assertTrue(Schema::hasIndex('password_histories', 'password_histories_user_id_created_at_index'));
    }

    public function test_personal_access_tokens_table_has_tokenable_index()
    {
        // PERFORMANCE FIX: Verify tokenable_type+tokenable_id composite index exists
        $this->assertTrue(Schema::hasIndex('personal_access_tokens', 'personal_access_tokens_tokenable_type_tokenable_id_index'));
    }

    public function test_personal_access_tokens_table_has_created_at_index()
    {
        // PERFORMANCE FIX: Verify created_at index exists for token expiration queries
        $this->assertTrue(Schema::hasIndex('personal_access_tokens', 'personal_access_tokens_created_at_index'));
    }

    public function test_jobs_table_has_queue_index()
    {
        // PERFORMANCE FIX: Verify queue index exists for job processing
        $this->assertTrue(Schema::hasIndex('jobs', 'jobs_queue_index'));
    }

    public function test_jobs_table_has_reserved_at_index()
    {
        // PERFORMANCE FIX: Verify reserved_at index exists for job status queries
        $this->assertTrue(Schema::hasIndex('jobs', 'jobs_reserved_at_index'));
    }
}
