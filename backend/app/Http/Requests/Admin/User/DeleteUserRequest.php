<?php
namespace App\Http\Requests\Admin\User;

use App\Http\Requests\Admin\AdminRequest;

class DeleteUserRequest extends AdminRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
