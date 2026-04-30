<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesApiController extends BaseApiController
{
    /**
     * Menampilkan daftar role dengan pagination.
     *
     * Endpoint ini mengembalikan daftar role dengan pagination. Akses dibatasi untuk super-admin.
     * Role dengan ID=1 (super-admin) tidak akan ditampilkan dalam daftar.
     *
     * @group Admin Roles
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman. Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[name] string Filter by name. Example: mitra
     * @queryParam sort string Sort field. Default: id. Example: name
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Roles retrieved",
     *   "data": [
     *     {
     *       "id": 2,
     *       "name": "mitra",
     *       "guard_name": "web",
     *       "created_at": "2024-12-14T12:02:50.000000Z",
     *       "updated_at": "2024-12-14T12:02:50.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 10,
     *     "has_more": false,
     *     "count": 1
     *   },
     *   "links": {
     *     "next_cursor": null,
     *     "next_page_url": null,
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://127.0.0.1:8000/api/admin/roles"
     *   },
     *   "pagination_info": {
     *     "total_page": 1,
     *     "total_records": 1
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }

        $perPage = $request->get('per_page', 5);

        // Filter out super-admin role (id 1)
        $roleQuery = Role::where('id', '!=', 1);
        
        // Get total count for pagination info (separate query for performance)
        $totalRecords = $roleQuery->count();

        $roles = \Spatie\QueryBuilder\QueryBuilder::for($roleQuery)
            ->allowedFilters(['name', 'guard_name'])
            ->defaultSort('id')
            ->allowedSorts(['id', 'name', 'created_at'])
            ->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($roles, 'Roles retrieved', 200, $extras);
    }

    /**
     * Get permission options for dropdowns/selects.
     *
     * Endpoint ini mengembalikan daftar permissions dalam format sederhana (id dan name)
     * untuk digunakan dalam dropdown atau select component. Mendukung pencarian berdasarkan nama.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Roles
     * @authenticated
     *
     * @queryParam search string Optional. Search permissions by name. Example: kegiatan
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Permission options retrieved",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "kegiatan-view"
     *     },
     *     {
     *       "id": 2,
     *       "name": "kegiatan-create"
     *     },
     *     {
     *       "id": 3,
     *       "name": "kegiatan-update"
     *     }
     *   ]
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function permissionOptions(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }

        $query = Permission::select('id', 'name');

        // Search by name if provided
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
            $permissions = $query->orderBy('name', 'asc')->get();
        } else {
            // If no search, return only first 8 permissions
            $permissions = $query->orderBy('name', 'asc')->limit(8)->get();
        }

        return $this->success($permissions, 'Permission options retrieved');
    }

    /**
     * Detail role.
     *
     * Endpoint ini mengembalikan detail role tertentu.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Roles
     * @authenticated
     *
     * @urlParam id int required ID role. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Role details",
     *   "data": {
     *     "id": 1,
     *     "name": "super-admin",
     *     "guard_name": "web",
     *     "created_at": "2024-12-14T08:07:15.000000Z",
     *     "updated_at": "2024-12-14T08:07:15.000000Z"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Role not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }
        $role = Role::find($id);
        if (!$role) {
             return $this->error('Role not found', null, 404);
        }
        return $this->success($role, 'Role details');
    }

    /**
     * Tambah role baru.
     *
     * Endpoint ini digunakan untuk membuat role baru.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Roles
     * @authenticated
     *
     * @bodyParam name string required Nama role. Contoh: katim
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Role created",
     *   "data": {
     *     "id": 3,
     *     "name": "katim",
     *     "guard_name": "web",
     *     "created_at": "2025-01-01T18:55:35.000000Z",
     *     "updated_at": "2025-01-01T18:55:35.000000Z"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": [
     *       "The name has already been taken."
     *     ]
     *   }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }
        
        // Convert name to lowercase
        $name = strtolower($request->input('name'));
        
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);
        
        $role = Role::create(['name' => $name]);
        return $this->success($role, 'Role created', 201);
    }

    /**
     * Ubah data role.
     *
     * Endpoint ini digunakan untuk memperbarui data role yang sudah ada.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Roles
     * @authenticated
     *
     * @urlParam id int required ID role. Contoh: 1
     *
     * @bodyParam name string required Nama role. Contoh: team-leader
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Role updated",
     *   "data": {
     *     "id": 1,
     *     "name": "team-leader",
     *     "guard_name": "web",
     *     "created_at": "2024-12-14T08:07:15.000000Z",
     *     "updated_at": "2025-01-01T19:00:00.000000Z"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Role not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "The name has already been taken."
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }
        $role = Role::find($id);
        if (!$role) {
             return $this->error('Role not found', null, 404);
        }
        
        // Convert name to lowercase
        $name = strtolower($request->input('name'));
        
        // Custom validation for case-insensitive uniqueness
        $request->merge(['name' => $name]);
        $validated = $request->validate([
            'name' => 'required|string',
        ]);
        
        // Check if another role already exists with this name (case-insensitive)
        $existingRole = Role::whereRaw('LOWER(name) = ?', [$name])
                           ->where('id', '!=', $id)
                           ->first();
                           
        if ($existingRole) {
            return $this->error('The name has already been taken.', null, 422);
        }
        
        $role->update(['name' => $name]);
        return $this->success($role, 'Role updated');
    }

    /**
     * Hapus role.
     *
     * Endpoint ini digunakan untuk menghapus role yang sudah ada.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Roles
     * @authenticated
     *
     * @urlParam id int required ID role. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Role berhasil dihapus",
     *   "data": null
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Role not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }
        $role = Role::find($id);
        if (!$role) {
             return $this->error('Role not found', null, 404);
        }
        // Ensure model_type is correct before detach/delete
        DB::table('model_has_roles')->where('role_id', $role->id)->update(['model_type' => 'App\\Models\\User']);
        // Detach role dari semua user sebelum menghapus
        \App\Models\User::whereHas('roles', function ($q) use ($role) {
            $q->where('id', $role->id);
        })->get()->each(function ($user) use ($role) {
            $user->removeRole($role);
        });
        $role->delete();
        return $this->success(null, 'Role berhasil dihapus');
    }

    /**
     * Update permissions for a role.
     *
     * Endpoint ini digunakan untuk mengupdate permissions yang dimiliki oleh role tertentu.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Roles
     * @authenticated
     *
     * @urlParam id int required ID role. Contoh: 2
     *
     * @bodyParam permission_ids array required Array of permission IDs. Contoh: [1, 2, 3]
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Role permissions updated successfully",
     *   "data": {
     *     "id": 2,
     *     "name": "mitra",
     *     "guard_name": "web",
     *     "permissions": [
     *       {
     *         "id": 1,
     *         "name": "kegiatan-view",
     *         "guard_name": "web"
     *       },
     *       {
     *         "id": 2,
     *         "name": "kegiatan-create",
     *         "guard_name": "web"
     *       }
     *     ]
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Role not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "permission_ids": ["The permission ids field is required."]
     *   }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePermissions(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }

        $role = Role::find($id);
        if (!$role) {
            return $this->error('Role not found', null, 404);
        }

        $validated = $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        // Get permission instances from IDs
        $permissions = Permission::whereIn('id', $validated['permission_ids'])->get();

        // Sync permissions to the role (removes old ones, adds new ones)
        $role->syncPermissions($permissions);

        // Reload role with permissions
        $role->load('permissions');

        return $this->success($role, 'Role permissions updated successfully');
    }
}
