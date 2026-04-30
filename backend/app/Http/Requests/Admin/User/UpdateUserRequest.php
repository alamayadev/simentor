<?php
namespace App\Http\Requests\Admin\User;

use App\Http\Requests\Admin\AdminRequest;

class UpdateUserRequest extends AdminRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name'          => 'sometimes|string|max:255',
            'email'         => 'sometimes|email|unique:users,email,' . $userId,
            'password'      => [
                'sometimes',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
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
                'description' => 'The password of the user. Must be at least 8 characters and contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
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
