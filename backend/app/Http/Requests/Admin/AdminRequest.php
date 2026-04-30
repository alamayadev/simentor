<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class AdminRequest extends ApiRequest
{
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Forbidden'
        ], 403));
    }
}
