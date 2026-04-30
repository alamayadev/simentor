<?php

namespace App\Http\Requests\Kantor\Pegawai;

use App\Http\Requests\ApiRequest;
use App\Services\PegawaiService;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class PegawaiRequest extends ApiRequest
{
    public function authorize(): bool
    {
        $service = app(PegawaiService::class);
        return $this->user() && $service->hasAccess($this->user());
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Forbidden'
        ], 403));
    }
}
