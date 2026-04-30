<?php
namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\QueryBuilder;

class UserService
{
    public function listUsers(array $filters = [], int $perPage = 10, ?int $organikPage = null, ?int $mitraPage = null): array
    {
        $cacheKey = 'users_index_' . md5(json_encode([
            'per_page' => $perPage,
            'filters' => $filters,
            'organik_page' => $organikPage,
            'mitra_page' => $mitraPage,
        ]));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters, $perPage, $organikPage, $mitraPage) {
            $organik = $this->getOrganikUsers($filters, $perPage, $organikPage);
            $mitra   = $this->getMitraUsers($filters, $perPage, $mitraPage);
            $roles   = Role::all();

            return [
                'organik'       => $organik['data'],
                'organik_total' => $organik['total'],
                'organik_meta'  => $organik['meta'],
                'organik_links' => $organik['links'],
                'mitra'         => $mitra['data'],
                'mitra_total'   => $mitra['total'],
                'mitra_meta'    => $mitra['meta'],
                'mitra_links'   => $mitra['links'],
                'roles'         => $roles,
            ];
        });
    }

    public function getOrganikUsers(array $filters = [], int $perPage = 10, ?int $page = null, string $pageName = 'organik_page'): array
    {
        $query = User::with(['roles', 'permissions'])
            ->where('id', '<', 100)
            ->where('id', '!=', 1);


        $total = QueryBuilder::for($query)
            ->allowedFilters(['name', 'email', 'roles.name'])
            ->count();

        $paginator = QueryBuilder::for($query)
            ->allowedFilters(['name', 'email', 'roles.name'])
            ->fastPaginate($perPage, ['*'], $pageName, $page);

        return [
            'data'  => $paginator,
            'total' => $total,
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page'      => $paginator->perPage(),
                'count'         => count($paginator->items()),
                'total_records' => $total,
                'total_page'    => $paginator->lastPage(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'path' => $paginator->path(),
            ],
        ];
    }

    public function getMitraUsers(array $filters = [], int $perPage = 10, ?int $page = null, string $pageName = 'mitra_page'): array
    {
        $query = User::with(['roles', 'permissions'])
            ->where('id', '>=', 100)
            ->where('id', '!=', 1);

        $total = QueryBuilder::for($query)
            ->allowedFilters(['name', 'email', 'roles.name'])
            ->count();

        $paginator = QueryBuilder::for($query)
            ->allowedFilters(['name', 'email', 'roles.name'])
            ->fastPaginate($perPage, ['*'], $pageName, $page);

        return [
            'data'  => $paginator,
            'total' => $total,
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page'      => $paginator->perPage(),
                'count'         => count($paginator->items()),
                'total_records' => $total,
                'total_page'    => $paginator->lastPage(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'path' => $paginator->path(),
            ],
        ];
    }

    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);

            if (isset($data['password'])) {
                $user->password = $data['password'];
                $user->save();
            }

            if (isset($data['roles'])) {
                $roles = Role::whereIn('name', $data['roles'])->get();
                $user->syncRoles($roles);
            }

            if (isset($data['permissions'])) {
                $permissions = Permission::whereIn('name', $data['permissions'])->get();
                $user->syncPermissions($permissions);
            }

            $user->load(['roles', 'permissions']);
            Cache::flush();

            return $user;
        });
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['password'])) {
                $this->validatePasswordHistory($user, $data['password']);

                if ($user->password) {
                    PasswordHistory::create([
                        'user_id'       => $user->id,
                        'password_hash' => $user->password,
                    ]);
                }
            }

            $user->update($data);

            if (isset($data['roles'])) {
                $roles = Role::whereIn('name', $data['roles'])->get();
                $user->syncRoles($roles);
            }

            if (isset($data['permissions'])) {
                $permissions = Permission::whereIn('name', $data['permissions'])->get();
                $user->syncPermissions($permissions);
            }

            $user->load(['roles', 'permissions']);
            Cache::flush();

            return $user;
        });
    }

    public function updateUserPermissions(User $user, array $permissionNames): User
    {
        return DB::transaction(function () use ($user, $permissionNames) {
            $permissions = Permission::whereIn('name', $permissionNames)->get();
            $user->syncPermissions($permissions);
            $user->load(['permissions']);
            Cache::flush();

            return $user;
        });
    }

    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->roles()->detach();
            $user->permissions()->detach();
            $user->delete();
            Cache::flush();
        });
    }

    public function bulkUpdateRoles(array $userIds, array $roleNames): array
    {
        $roles = Role::whereIn('name', $roleNames)->get();

        return DB::transaction(function () use ($userIds, $roles) {
            $updatedCount  = 0;
            $failedUpdates = [];

            foreach ($userIds as $userId) {
                try {
                    $targetUser = User::findOrFail($userId);
                    $targetUser->syncRoles($roles);
                    $updatedCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to update roles for user ID {$userId}: " . $e->getMessage());
                    $failedUpdates[] = [
                        'user_id' => $userId,
                        'error'   => $e->getMessage(),
                    ];
                }
            }

            Cache::flush();

            return [
                'updated_users'  => $updatedCount,
                'failed_updates' => $failedUpdates,
            ];
        });
    }

    protected function validatePasswordHistory(User $user, string $newPassword): void
    {
        $recentPasswords = PasswordHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->pluck('password_hash');

        foreach ($recentPasswords as $oldHash) {
            if (password_verify($newPassword, $oldHash) || bcrypt($newPassword) === $oldHash) {
                throw new \Exception('Password reuse detected');
            }
        }
    }
}
