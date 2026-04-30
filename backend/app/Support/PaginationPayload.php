<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginationPayload
{
    public static function fromPaginator(CursorPaginator|PaginatorContract $paginator): array
    {
        if ($paginator instanceof CursorPaginator) {
            $paginatorData = $paginator->toArray();

            return [
                'data' => $paginatorData['data'],
                'meta' => [
                    'per_page' => $paginator->perPage(),
                    'has_more' => $paginator->hasMorePages(),
                    'count' => count($paginatorData['data']),
                ],
                'links' => [
                    'first' => null,
                    'last' => null,
                    'prev' => $paginatorData['prev_page_url'] ?? null,
                    'next' => $paginatorData['next_page_url'] ?? null,
                    'path' => $paginatorData['path'] ?? null,
                    'next_cursor' => $paginatorData['next_cursor'] ?? null,
                    'next_page_url' => $paginatorData['next_page_url'] ?? null,
                    'prev_cursor' => $paginatorData['prev_cursor'] ?? null,
                    'prev_page_url' => $paginatorData['prev_page_url'] ?? null,
                ],
                'pagination_info' => [
                    'total_page' => null,
                    'total_records' => null,
                ],
            ];
        }

        if ($paginator instanceof LengthAwarePaginator) {
            return [
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'from' => $paginator->firstItem(),
                    'last_page' => $paginator->lastPage(),
                    'path' => $paginator->path(),
                    'per_page' => $paginator->perPage(),
                    'to' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                    'count' => count($paginator->items()),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'path' => $paginator->path(),
                    'next_cursor' => null,
                    'next_page_url' => $paginator->nextPageUrl(),
                    'prev_cursor' => null,
                    'prev_page_url' => $paginator->previousPageUrl(),
                ],
                'pagination_info' => [
                    'total_page' => $paginator->lastPage(),
                    'total_records' => $paginator->total(),
                ],
            ];
        }

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'count' => count($paginator->items()),
            ],
            'links' => [
                'first' => null,
                'last' => null,
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'path' => $paginator->path(),
                'next_cursor' => null,
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_cursor' => null,
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
            'pagination_info' => [
                'total_page' => null,
                'total_records' => null,
            ],
        ];
    }
}
