<?php
namespace App\Http\Requests\Admin\User;

use App\Http\Requests\Admin\AdminRequest;

class BulkUpdateUserRolesRequest extends AdminRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'user_ids'   => 'required|array',
            'user_ids.*' => 'required|integer|exists:users,id',
            'roles'      => 'required|array',
            'roles.*'    => 'required|string|exists:roles,name',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'user_ids'   => [
                'description' => 'The IDs of the users to update.',
                'example'     => [1, 2, 3],
            ],
            'user_ids.*' => [
                'description' => 'The ID of the user.',
                'example'     => 1,
            ],
            'roles'      => [
                'description' => 'The roles to assign to the users.',
                'example'     => ['admin'],
            ],
            'roles.*'    => [
                'description' => 'The name of the role.',
                'example'     => 'admin',
            ],
        ];
    }
}
