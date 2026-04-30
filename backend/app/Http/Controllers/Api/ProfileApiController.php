<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Pegawai;

/**
 * Profile API
 *
 * Endpoints for managing the authenticated user's profile, including personal details,
 * linked employee (Pegawai) data, and password updates.
 *
 * @group Profile
 * @authenticated
 */
class ProfileApiController extends BaseApiController
{
    /**
     * Get User Profile
     *
     * Returns authenticated user's data with roles and permissions.
     * Includes user's assigned roles and all permissions from those roles.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User profile retrieved",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "email_verified_at": "2023-01-01T00:00:00.000000Z",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z",
     *     "profile_photo_url": "...",
     *     "roles": [
     *       {
     *         "id": 1,
     *         "name": "super-admin",
     *         "guard_name": "web",
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ],
     *     "permissions": [
     *       {
     *         "id": 1,
     *         "name": "view users",
     *         "guard_name": "web",
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     */
    public function show(Request $request)
    {
        $user = $request->user()->load(['roles', 'permissions', 'pegawai']);
        return $this->success($user, 'User profile retrieved');
    }

    /**
     * Update User Profile
     *
     * Updates the authenticated user's name and email.
     *
     * @bodyParam name string required The user's name. Example: John Doe
     * @bodyParam email string required The user's email. Example: john@example.com
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User profile updated",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "email_verified_at": "2023-01-01T00:00:00.000000Z",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z",
     *     "profile_photo_url": "..."
     *   }
     * }
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return $this->success($user, 'User profile updated');
    }

    /**
     * Get Linked Pegawai Profile
     *
     * Returns the Pegawai data linked to the authenticated user.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pegawai profile retrieved",
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "nip": "1234567890",
     *     "gelar_depan": "Dr.",
     *     "gelar_belakang": "S.Kom",
     *     "tempat_lahir": "Jakarta",
     *     "tanggal_lahir": "1990-01-01",
     *     "alamat": "Jl. Sudirman No. 1",
     *     "no_hp": "081234567890",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Pegawai profile not found"
     * }
     */
    public function showPegawai(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        if (!$pegawai) {
            return $this->error('Pegawai profile not found', null, 404);
        }

        return $this->success($pegawai, 'Pegawai profile retrieved');
    }

    /**
     * Update Linked Pegawai Profile
     *
     * Updates the Pegawai data linked to the authenticated user.
     * Can update any field in the pegawai table except id and user_id.
     *
     * @bodyParam nip string NIP pegawai. Example: 199001012020121001
     * @bodyParam gelar_depan string Gelar depan. Example: Dr.
     * @bodyParam gelar_belakang string Gelar belakang. Example: S.Kom
     * @bodyParam tempat_lahir string Tempat lahir. Example: Jakarta
     * @bodyParam tanggal_lahir date Tanggal lahir (YYYY-MM-DD). Example: 1990-01-01
     * @bodyParam alamat string Alamat lengkap. Example: Jl. Jend. Sudirman Kav 1
     * @bodyParam no_hp string Nomor HP. Example: 08123456789
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pegawai profile updated",
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "nip": "199001012020121001",
     *     "gelar_depan": "Dr.",
     *     "gelar_belakang": "S.Kom",
     *     "tempat_lahir": "Jakarta",
     *     "tanggal_lahir": "1990-01-01",
     *     "alamat": "Jl. Jend. Sudirman Kav 1",
     *     "no_hp": "08123456789",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function updatePegawai(Request $request)
    {
        $user = $request->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return $this->error('Pegawai profile not found', null, 404);
        }

        // Validate that the request does not try to update id or user_id
        if ($request->has('id') || $request->has('user_id')) {
            return $this->error('Cannot update id or user_id', null, 422);
        }

        // Allow updating all other fields present in the request
        // Using $request->all() but filtering out guarded is handled by model,
        // but we explicitly remove id/user_id to be safe.
        $data = $request->except(['id', 'user_id', '_method', '_token']);

        $pegawai->update($data);

        return $this->success($pegawai, 'Pegawai profile updated');
    }

    /**
     * Update Password
     *
     * Updates the authenticated user's password.
     *
     * @bodyParam current_password string required The current password.
     * @bodyParam password string required The new password (min 8 chars, confirmed).
     * @bodyParam password_confirmation string required The new password confirmation.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Password updated successfully",
     *   "data": null
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "current_password": [
     *       "The provided password does not match your current password."
     *     ]
     *   }
     * }
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $user->password = $validated['password']; // Will be hashed by setPasswordAttribute mutator
        $user->save();

        return $this->success(null, 'Password updated successfully');
    }
}
