<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\Permission\Models\Permission;

class PermissionsApiController extends BaseApiController
{
    /**
     * Menampilkan daftar permission dengan pagination.
     *
     * Endpoint ini mengembalikan daftar permission untuk digunakan pada manajemen akses.
     * Akses dibatasi untuk super-admin.
     *
     * @group Admin Permissions
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman. Contoh: 15
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[name] string Filter by name. Example: manage-users
     * @queryParam sort string Sort field. Default: id. Example: name
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Permissions retrieved",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "manage-users",
     *       "guard_name": "web",
     *       "created_at": "2024-12-14T08:07:15.000000Z",
     *       "updated_at": "2024-12-14T12:02:50.000000Z"
     *     },
     *     {
     *       "id": 2,
     *       "name": "manage-roles",
     *       "guard_name": "web",
     *       "created_at": "2024-12-14T12:02:50.000000Z",
     *       "updated_at": "2024-12-14T12:02:50.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 15,
     *     "has_more": false,
     *     "count": 2
     *   },
     *   "links": {
     *     "next_cursor": null,
     *     "next_page_url": null,
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://127.0.0.1:8000/api/admin/permissions"
     *   },
     *   "pagination_info": {
     *     "total_page": 1,
     *     "total_records": 2
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
        $user = Auth::user();
        if (! $user || ! $user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }

        $perPage = $request->get('per_page', 4);
        $query   = Permission::query();

        $filterName = $request->input('filter.name');
        if ($filterName !== null && $filterName !== '') {
            $query->where('name', 'like', '%' . $filterName . '%');
        }

        $sort      = $request->input('sort', 'id');
        $direction = 'asc';
        if ($sort === 'created_at') {
            $direction = 'desc';
        }

        if (! in_array($sort, ['id', 'name', 'created_at'], true)) {
            $sort = 'id';
        }

        $totalRecords = $query->count();

        $permissions = $query->orderBy($sort, $direction)->fastPaginate($perPage);

        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras     = [
            'pagination_info' => [
                'total_page'    => $totalPages,
                'total_records' => $totalRecords,
            ],
        ];

        return $this->success($permissions, 'Permissions retrieved', 200, $extras);
    }

    /**
     * Menampilkan daftar permission yang dikelompokkan berdasarkan prefix.
     *
     * Endpoint ini mengembalikan satu item per prefix permission, beserta daftar
     * permission yang berada di dalam prefix tersebut. Cocok untuk tampilan kartu
     * per modul/resource di frontend.
     *
     * @group Admin Permissions
     * @authenticated
     *
     * @queryParam per_page int Jumlah grup per halaman. Contoh: 10
     * @queryParam page int Halaman aktif. Contoh: 1
     * @queryParam filter[prefix] string Filter berdasarkan prefix. Contoh: raw-data
     * @queryParam filter[search] string Alias filter untuk prefix. Contoh: kegiatan
     * @queryParam sort string Field sort. Default: prefix. Contoh: permissions_count
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Permission groups retrieved",
     *   "data": [
     *     {
     *       "id": 10,
     *       "prefix": "raw-data",
     *       "guard_name": "sanctum",
     *       "permissions_count": 4,
     *       "permissions": [
     *         {"id": 10, "name": "raw-data-view", "guard_name": "sanctum"},
     *         {"id": 11, "name": "raw-data-create", "guard_name": "sanctum"}
     *       ]
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "last_page": 1,
     *     "per_page": 10,
     *     "total": 1
     *   }
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function groupedByPrefix(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? $perPage : 10;

        $prefixExpression = $this->permissionPrefixSql();

        $groupQuery = Permission::query()
            ->selectRaw($prefixExpression . ' as prefix')
            ->selectRaw('MIN(id) as id')
            ->selectRaw('MIN(guard_name) as guard_name')
            ->selectRaw('COUNT(*) as permissions_count')
            ->groupBy('prefix');

        $filterPrefix = $request->input('filter.prefix', $request->input('filter.search'));
        if ($filterPrefix !== null && $filterPrefix !== '') {
            $groupQuery->having('prefix', 'like', '%' . $filterPrefix . '%');
        }

        $sort = $request->input('sort', 'prefix');
        if (! in_array($sort, ['prefix', 'permissions_count', 'id'], true)) {
            $sort = 'prefix';
        }

        $direction = $sort === 'permissions_count' ? 'desc' : 'asc';
        $groupQuery->orderBy($sort, $direction);

        /** @var LengthAwarePaginator $groups */
        $groups = $groupQuery->fastPaginate($perPage);

        $groupItems = collect($groups->items());
        $prefixes   = $groupItems->pluck('prefix')->filter()->values()->all();

        $permissionsByPrefix = collect();
        if ($prefixes !== []) {
            $permissionsByPrefix = Permission::query()
                ->select(['id', 'name', 'guard_name'])
                ->selectRaw($prefixExpression . ' as prefix')
                ->where(function ($query) use ($prefixExpression, $prefixes) {
                    foreach ($prefixes as $prefix) {
                        $query->orWhereRaw($prefixExpression . ' = ?', [$prefix]);
                    }
                })
                ->orderBy('name', 'asc')
                ->get()
                ->groupBy('prefix');
        }

        $groups->setCollection(
            $groupItems->map(function ($group) use ($permissionsByPrefix) {
                $permissions = $permissionsByPrefix->get($group->prefix, collect())
                    ->values()
                    ->map(function ($permission) {
                        return [
                            'id'         => (int) $permission->id,
                            'name'       => $permission->name,
                            'guard_name' => $permission->guard_name,
                        ];
                    })
                    ->all();

                return [
                    'id'                => (int) $group->id,
                    'prefix'            => $group->prefix,
                    'guard_name'        => $group->guard_name,
                    'permissions_count' => (int) $group->permissions_count,
                    'permissions'       => $permissions,
                ];
            })
        );

        return $this->success($groups, 'Permission groups retrieved');
    }

    private function permissionPrefixSql(): string
    {
        return "CASE
            WHEN name = '*' THEN '*'
            WHEN name LIKE '%-view' THEN SUBSTR(name, 1, LENGTH(name) - 5)
            WHEN name LIKE '%-create' THEN SUBSTR(name, 1, LENGTH(name) - 7)
            WHEN name LIKE '%-update' THEN SUBSTR(name, 1, LENGTH(name) - 7)
            WHEN name LIKE '%-delete' THEN SUBSTR(name, 1, LENGTH(name) - 7)
            WHEN name LIKE '%.*' THEN SUBSTR(name, 1, LENGTH(name) - 2)
            WHEN name LIKE '%.read' THEN SUBSTR(name, 1, LENGTH(name) - 5)
            WHEN name LIKE '%.write' THEN SUBSTR(name, 1, LENGTH(name) - 6)
            WHEN name LIKE '%.create' THEN SUBSTR(name, 1, LENGTH(name) - 7)
            WHEN name LIKE '%.update' THEN SUBSTR(name, 1, LENGTH(name) - 7)
            WHEN name LIKE '%.delete' THEN SUBSTR(name, 1, LENGTH(name) - 7)
            ELSE name
        END";
    }

    private function invalidateApiControllersCache(): void
    {
        Cache::forget('api_controllers_list');
    }

    /**
     * Menampilkan detail permission.
     *
     * Endpoint ini mengembalikan detail permission tertentu.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Permissions
     * @authenticated
     *
     * @urlParam id int required ID permission. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Permission details",
     *   "data": {
     *     "id": 1,
     *     "name": "manage-users",
     *     "guard_name": "web",
     *     "created_at": "2024-12-14T08:07:15.000000Z",
     *     "updated_at": "2024-12-14T12:02:50.000000Z"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Permission not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }
        $permission = Permission::find($id);
        if (! $permission) {
            return $this->error('Permission not found', null, 404);
        }
        return $this->success($permission, 'Permission details');
    }

    /**
     * Membuat permission baru.
     *
     * Endpoint ini digunakan untuk membuat permission baru.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Permissions
     * @authenticated
     *
     * @bodyParam name string required Nama permission. Contoh: manage-permissions
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Permission created",
     *   "data": {
     *     "id": 3,
     *     "name": "manage-permissions",
     *     "guard_name": "sanctum",
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
        $user = Auth::user();
        if (! $user || ! $user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);
        $permission = Permission::create([
            'name'       => $validated['name'],
            'guard_name' => 'sanctum',
        ]);

        $this->invalidateApiControllersCache();

        return $this->success($permission, 'Permission created', 201);
    }

    /**
     * Bulk create permissions for a given resource.
     *
     * Endpoint ini digunakan untuk membuat banyak permission sekaligus berdasarkan prefix.
     * Akan membuat permission dengan suffix: -view, -create, -update, dan -delete.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Permissions
     * @authenticated
     *
     * @bodyParam prefix string required Prefix nama permission. Contoh: raw-data
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Bulk permissions created",
     *   "data": {
     *     "created": [
     *       {"id": 10, "name": "raw-data-view", "guard_name": "sanctum"},
     *       {"id": 11, "name": "raw-data-create", "guard_name": "sanctum"},
     *       {"id": 12, "name": "raw-data-update", "guard_name": "sanctum"},
     *       {"id": 13, "name": "raw-data-delete", "guard_name": "sanctum"}
     *     ],
     *     "existing": []
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "prefix": [
     *       "The prefix field is required."
     *     ]
     *   }
     * }
     *
     * @param  IlluminateHttpRequest  $request
     * @return IlluminateHttpResponse
     */
    public function bulkCreate(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }

        $validated = $request->validate([
            'prefix' => 'required|string|max:255',
        ]);

        $prefix   = $validated['prefix'];
        $suffixes = ['-view', '-create', '-update', '-delete'];

        $createdPermissions  = [];
        $existingPermissions = [];

        foreach ($suffixes as $suffix) {
            $permissionName = $prefix . $suffix;

            $existingPermission = Permission::where('name', $permissionName)->first();

            if ($existingPermission) {
                $existingPermissions[] = $existingPermission;
            } else {
                $permission = Permission::create([
                    'name'       => $permissionName,
                    'guard_name' => 'sanctum',
                ]);
                $createdPermissions[] = $permission;
            }
        }

        $this->invalidateApiControllersCache();

        return $this->success([
            'created'  => $createdPermissions,
            'existing' => $existingPermissions,
        ], 'Bulk permissions created', 201);
    }

    /**
     * Memperbarui data permission.
     *
     * Endpoint ini digunakan untuk memperbarui data permission yang sudah ada.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Permissions
     * @authenticated
     *
     * @urlParam id int required ID permission. Contoh: 1
     *
     * @bodyParam name string required Nama permission. Contoh: manage-all-permissions
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Permission updated",
     *   "data": {
     *     "id": 1,
     *     "name": "manage-all-permissions",
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
     *   "message": "Permission not found"
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }
        $permission = Permission::find($id);
        if (! $permission) {
            return $this->error('Permission not found', null, 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $id,
        ]);
        $permission->update(['name' => $validated['name']]);

        $this->invalidateApiControllersCache();

        return $this->success($permission, 'Permission updated');
    }

    /**
     * Menghapus permission.
     *
     * Endpoint ini digunakan untuk menghapus permission yang sudah ada.
     * Hanya super-admin yang dapat mengakses endpoint ini.
     *
     * @group Admin Permissions
     * @authenticated
     *
     * @urlParam id int required ID permission. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Permission berhasil dihapus",
     *   "data": null
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Forbidden"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Permission not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }
        $permission = Permission::find($id);
        if (! $permission) {
            return $this->error('Permission not found', null, 404);
        }

        DB::table('model_has_permissions')
            ->where('permission_id', $permission->id)
            ->update(['model_type' => 'App\\Models\\User']);
        $connection = DB::connection();
        $driver     = $connection->getDriverName();
        if ($driver === 'sqlite') {
            $connection->statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'mysql') {
            $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        }

        DB::table('permissions')->where('id', $permission->id)->delete();

        if ($driver === 'sqlite') {
            $connection->statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->invalidateApiControllersCache();

        return $this->success(null, 'Permission berhasil dihapus');
    }

    /**
     * List all API controllers except Admin and Auth controllers.
     *
     * This endpoint returns a list of all available API controllers,
     * excluding Admin and Auth related controllers, along with CRUD actions.
     * Used for form options in the frontend.
     *
     * @group Admin Permissions
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Controllers list",
     *   "data": {
     *     "controllers": [
     *       "surveycraft",
     *       "alokasi",
     *       "cek-georef",
     *       "cek-scan"
     *     ],
     *     "actions": [
     *       "create",
     *       "view",
     *       "update",
     *       "delete"
     *     ]
     *   }
     * }
     * @response 403 {
     *   "message": "Forbidden"
     * }
     */
    public function listApiControllers()
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('super-admin')) {
            return $this->error('Forbidden', null, 403);
        }

        $controllersData = Cache::remember('system_api_controllers_file_list', 86400, function () {
            $controllers     = [];
            $controllersPath = app_path('Http/Controllers/Api');

            if (is_dir($controllersPath)) {
                $files = new RecursiveDirectoryIterator($controllersPath);
                $iter  = new RecursiveIteratorIterator($files);

                while ($iter->valid()) {
                    if (! $iter->isDot() && $iter->isFile() && pathinfo($iter->getSubPathName(), PATHINFO_EXTENSION) === 'php') {
                        $fullPath     = $iter->key();
                        $relativePath = str_replace([$controllersPath . '\\', '.php'], '', $fullPath);
                        $parts        = explode('\\', $relativePath);

                        if (count($parts) >= 2) {
                            $folder         = strtolower($parts[0]);
                            $controllerName = strtolower(str_replace('Controller', '', $parts[count($parts) - 1]));

                            if (! in_array($folder, ['admin', 'auth', 'kantor', 'ipds', 'miniapp'])) {
                                $controllers[] = $controllerName;
                            }
                        }
                    }
                    $iter->next();
                }
            }

            $controllers = array_unique($controllers);
            sort($controllers);

            return [
                'controllers' => $controllers,
                'actions'     => ['create', 'view', 'update', 'delete'],
            ];
        });

        return $this->success($controllersData, 'Controllers list');
    }
}
