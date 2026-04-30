<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PaginationPayload;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;

class BaseApiController extends Controller
{
    /**
     * Send a success response.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function success($data = [], $message = null, $code = 200, $extras = [])
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data instanceof CursorPaginator || $data instanceof PaginatorContract) {
            $response = array_merge($response, PaginationPayload::fromPaginator($data));
        } else {
            $response['data'] = $data;
        }

        if (!empty($extras)) {
            $response = array_merge($response, $extras);
        }

        return response()->json($response, $code);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $code
     * @return JsonResponse
     */
    protected function error($message, $errors = null, $code = 400)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
