<?php
namespace App\Http\Requests\Admin\User;

use App\Http\Requests\Admin\AdminRequest;

class UpdateUserPermissionsRequest extends AdminRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'permissions'   => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'The permissions field is required.',
            'permissions.*.exists' => 'One or more permissions do not exist in the system.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'permissions'   => [
                'description' => 'The permissions to assign to the user.',
                'example'     => ['edit posts'],
            ],
            'permissions.*' => [
                'description' => 'The name of the permission.',
                'example'     => 'edit posts',
            ],
        ];
    }
}
