<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function assertUnifiedPaginationStructure($response): void
    {
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
                'count',
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
                'path',
                'next_cursor',
                'next_page_url',
                'prev_cursor',
                'prev_page_url',
            ],
            'pagination_info' => [
                'total_page',
                'total_records',
            ],
        ]);
    }

    protected function assertPaginationHasNextPage($response): void
    {
        $this->assertNotNull($response->json('links.next'));
        $this->assertNotNull($response->json('links.next_page_url'));
    }

    protected function assertPaginationHasNoNextPage($response): void
    {
        $this->assertNull($response->json('links.next'));
        $this->assertNull($response->json('links.next_page_url'));
    }
}
