<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\BaseApiController;
use App\Http\Requests\Admin\User\BulkUpdateUserRolesRequest;
use App\Http\Requests\Admin\User\DeleteUserRequest;
use App\Http\Requests\Admin\User\ShowUserRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserPermissionsRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @group Admin - User Management
 */
class UserApiController extends BaseApiController
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Menampilkan daftar pengguna (Organik & Mitra).
     *
     * Endpoint ini mengembalikan daftar pengguna yang dibagi menjadi dua kategori: Organik dan Mitra.
     * Mendukung pagination, filtering, dan cursor pagination.
     *
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 10). Example: 10
     * @queryParam filter[name] string Filter berdasarkan nama. Example: Budi
     * @queryParam filter[email] string Filter berdasarkan email. Example: budi@example.com
     * @queryParam organik_page int Halaman untuk pagination data organik.
     * @queryParam mitra_page int Halaman untuk pagination data mitra.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "organik_data": [
     *       {
     *         "id": 1,
     *         "name": "Admin Organik",
     *         "email": "admin@bps.go.id",
     *         "roles": ["super-admin"]
     *       }
     *     ],
     *     "mitra_data": [
     *       {
     *         "id": 10,
     *         "name": "Mitra Statistik",
     *         "email": "mitra@example.com",
     *         "roles": ["mitra"]
     *       }
     *     ],
     *     "roles": [
     *       {"id": 1, "name": "super-admin"},
     *       {"id": 2, "name": "mitra"}
     *     ],
     *     "organik_meta": {
     *       "per_page": 10
     *     },
     *     "mitra_meta": {
     *       "per_page": 10
     *     }
     *   }
     * }
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $result  = $this->userService->listUsers(
            $request->get('filter', []),
            $perPage,
            $request->integer('organik_page') ?: null,
            $request->integer('mitra_page') ?: null
        );

        $response = [
            'organik_data'  => UserResource::collection($result['organik']),
            'mitra_data'    => UserResource::collection($result['mitra']),
            'roles'         => $result['roles'],
            'organik_meta'  => $result['organik_meta'],
            'organik_links' => $result['organik_links'],
            'mitra_meta'    => $result['mitra_meta'],
            'mitra_links'   => $result['mitra_links'],
        ];

        return $this->success($response, 'Data retrieved successfully');
    }

    /**
     * Menampilkan detail pengguna.
     *
     * Endpoint ini mengembalikan detail pengguna beserta roles dan permissions yang dimiliki.
     *
     * @authenticated
     *
     * @urlParam id int required ID Pengguna. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User details",
     *   "data": {
     *     "id": 1,
     *     "name": "Admin Organik",
     *     "email": "admin@bps.go.id",
     *     "roles": [
     *       {"id": 1, "name": "super-admin"}
     *     ],
     *     "permissions": []
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "User not found"
     * }
     */
    public function show(ShowUserRequest $request, $id)
    {
        $targetUser = User::with(['roles', 'permissions'])->find($id);

        if (! $targetUser) {
            return $this->error('User not found', null, 404);
        }

        return $this->success($targetUser, 'User details');
    }

    /**
     * Membuat pengguna baru.
     *
     * Endpoint ini digunakan untuk membuat pengguna baru dengan roles dan permissions tertentu.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @authenticated
     *
     * @bodyParam name string required Nama lengkap pengguna. Example: Budi Santoso
     * @bodyParam email string required Email pengguna (unik). Example: budi@example.com
     * @bodyParam password string required Password (min 8 characters). Example: Password123!
     * @bodyParam roles string[] required Roles yang akan diberikan. Example: ["admin"]
     * @bodyParam permissions string[] required Permissions yang akan diberikan. Example: ["manage users"]
     *
     * @response 201 {
     *   "success": true,
     *   "message": "User created",
     *   "data": {
     *     "user": {
     *       "id": 15,
     *       "name": "Budi Santoso",
     *       "email": "budi@example.com"
     *     },
     *     "roles": [],
     *     "user_roles": [],
     *     "user_permissions": []
     *   }
     * }
     */
    public function store(StoreUserRequest $request)
    {
        $newUser = $this->userService->createUser($request->validated());
        $roles   = Role::all();

        return $this->success([
            'user'             => $newUser,
            'roles'            => $roles,
            'user_roles'       => $newUser->roles,
            'user_permissions' => $newUser->permissions,
        ], 'User created', 201);
    }

    /**
     * Memperbarui data pengguna.
     *
     * Endpoint ini digunakan untuk memperbarui data pengguna termasuk roles dan permissions.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @authenticated
     *
     * @urlParam id int required ID Pengguna. Example: 1
     *
     * @bodyParam name string Nama lengkap pengguna. Example: Budi Santoso Updated
     * @bodyParam email string Email pengguna. Example: budi_updated@example.com
     * @bodyParam password string Password baru (opsional). Example: NewPassword123!
     * @bodyParam roles string[] Roles yang akan diberikan. Example: ["admin", "editor"]
     * @bodyParam permissions string[] Permissions yang akan diberikan. Example: ["manage content"]
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User updated",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Budi Santoso Updated"
     *     }
     *   }
     * }
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $targetUser = User::find($id);
        if (! $targetUser) {
            return $this->error('User not found', null, 404);
        }

        try {
            $this->userService->updateUser($targetUser, $request->validated());
            $roles = Role::all();

            return $this->success([
                'user'             => $targetUser,
                'roles'            => $roles,
                'user_roles'       => $targetUser->roles,
                'user_permissions' => $targetUser->permissions,
            ], 'User updated');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 422);
        }
    }

    /**
     * Memperbarui permissions pengguna.
     *
     * Endpoint ini khusus untuk memperbarui direct permissions pengguna.
     *
     * @authenticated
     *
     * @urlParam id int required ID Pengguna. Example: 1
     *
     * @bodyParam permissions string[] required Daftar permission baru. Example: ["view dashboard", "manage settings"]
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User permissions updated successfully"
     * }
     */
    public function updatePermissions(UpdateUserPermissionsRequest $request, $id)
    {
        $targetUser = User::find($id);
        if (! $targetUser) {
            return $this->error('User not found', null, 404);
        }

        try {
            $this->userService->updateUserPermissions($targetUser, $request->input('permissions'));
            $allPermissions = Permission::all();

            return $this->success([
                'user'            => [
                    'id'    => $targetUser->id,
                    'name'  => $targetUser->name,
                    'email' => $targetUser->email,
                ],
                'permissions'     => $targetUser->permissions,
                'all_permissions' => $allPermissions,
            ], 'User permissions updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update permissions: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Menghapus pengguna.
     *
     * Endpoint ini digunakan untuk menghapus pengguna dari sistem secara permanen.
     *
     * @authenticated
     *
     * @urlParam id int required ID Pengguna. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User deleted"
     * }
     */
    public function destroy(DeleteUserRequest $request, $id)
    {
        $targetUser = User::find($id);
        if (! $targetUser) {
            return $this->error('User not found', null, 404);
        }

        $this->userService->deleteUser($targetUser);

        return $this->success(null, 'User deleted');
    }

    /**
     * Menampilkan daftar pengguna Organik.
     *
     * Endpoint ini mengembalikan daftar pengguna kategori Organik saja.
     *
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman. Example: 10
     * @queryParam filter[name] string Filter nama. Example: Admin
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data organik",
     *   "data": [...]
     * }
     */
    public function organik(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page  = $request->integer('page') ?: null;

        // Debug logging
        Log::info('organik endpoint called', [
            'per_page'         => $perPage,
            'page'             => $page,
            'all_request_data' => $request->all(),
            'query_params'     => $request->query->all(),
        ]);

        $result = $this->userService->getOrganikUsers($request->get('filter', []), $perPage, $page, 'page');

        $extras = [
            'roles'           => Role::all(),
            'pagination_info' => [
                'total_page'    => $result['meta']['total_page'],
                'total_records' => $result['meta']['total_records'],
            ],
        ];

        return $this->success($result['data'], 'Data organik', 200, $extras);
    }

    /**
     * Menampilkan daftar pengguna Mitra.
     *
     * Endpoint ini mengembalikan daftar pengguna kategori Mitra saja.
     *
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman. Example: 10
     * @queryParam filter[name] string Filter nama. Example: Budi
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data mitra",
     *   "data": [...]
     * }
     */
    public function mitra(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page  = $request->integer('page') ?: null;
        $result  = $this->userService->getMitraUsers($request->get('filter', []), $perPage, $page, 'page');

        $extras = [
            'roles'           => Role::all(),
            'pagination_info' => [
                'total_page'    => $result['meta']['total_page'],
                'total_records' => $result['meta']['total_records'],
            ],
        ];

        return $this->success($result['data'], 'Data mitra', 200, $extras);
    }

    /**
     * Update Role Pengguna Massal.
     *
     * Endpoint ini digunakan untuk mengubah role beberapa pengguna sekaligus.
     *
     * @authenticated
     *
     * @bodyParam user_ids int[] required ID Pengguna yang akan diupdate. Example: [1, 2, 3]
     * @bodyParam roles string[] required Role baru yang akan diberikan. Example: ["editor"]
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Roles updated successfully"
     * }
     */
    public function bulkUpdateRoles(BulkUpdateUserRolesRequest $request)
    {
        try {
            $result = $this->userService->bulkUpdateRoles(
                $request->input('user_ids'),
                $request->input('roles')
            );

            return $this->success($result, 'Roles updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update roles: ' . $e->getMessage(), null, 500);
        }
    }
}
