<?php
namespace App\Http\Requests\Admin\User;

use App\Http\Requests\Admin\AdminRequest;

class StoreUserRequest extends AdminRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => [
                'required',
                'string',
                'min:8',
            ],
            'roles'         => 'sometimes|array',
            'roles.*'       => 'string|exists:roles,name',
            'permissions'   => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name'          => [
                'description' => 'The name of the user.',
                'example'     => 'John Doe',
            ],
            'email'         => [
                'description' => 'The email of the user.',
                'example'     => 'john@example.com',
            ],
            'password'      => [
                'description' => 'The password of the user. Must be at least 8 characters.',
                'example'     => 'Password123!',
            ],
            'roles'         => [
                'description' => 'The roles to assign to the user.',
                'example'     => ['admin'],
            ],
            'roles.*'       => [
                'description' => 'The name of the role.',
                'example'     => 'admin',
            ],
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
