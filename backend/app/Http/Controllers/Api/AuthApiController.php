<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthApiController extends BaseApiController
{
    /**
     * Login pengguna dan dapatkan token API.
     *
     * Endpoint ini digunakan untuk masuk (login) dan menghasilkan token API yang dapat digunakan
     * untuk mengakses endpoint yang dilindungi.
     *
     * @group Authentication
     *
     * @bodyParam email string required Email pengguna. Contoh: admin@admin.com
     * @bodyParam password string required Password pengguna. Contoh: password123
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Login successful",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Admin",
     *       "email": "admin@admin.com",
     *       "email_verified_at": null,
     *       "current_team_id": null,
     *       "profile_photo_path": null,
     *       "created_at": "2024-01-03T02:00:56.000000Z",
     *       "updated_at": "2024-12-02T18:29:58.000000Z"
     *     },
     *     "roles": [...],
     *     "permissions": [...],
     *     "token": "1|abcdefghijklmnopqrstuvwxyz123456"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid credentials"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $authGuard = Auth::guard('web');

        if (!$authGuard->attempt($credentials)) {
            // SECURITY FIX: Log failed login attempts with structured data
            Log::channel('security')->warning('Authentication failed', [
                'event' => 'login_failed',
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);

            return $this->error('Invalid credentials', null, 401);
        }

        // Get the authenticated user ID and re-fetch the user with roles, permissions, and related pegawai
        $userId = $authGuard->id();
        $user = User::with(['roles', 'permissions', 'pegawai:id,user_id,nama,nip,jabatan,pangkat,gol'])->find($userId);
        $token = $user->createToken('api-token')->plainTextToken;

        // SECURITY FIX: Log successful login with structured data
        Log::channel('security')->info('Authentication successful', [
            'event' => 'login_success',
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * Logout pengguna dan hapus semua token API.
     *
     * Endpoint ini digunakan untuk keluar (logout) dan menghapus semua token API yang terkait
     * dengan pengguna tersebut.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Logged out",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // Delete all tokens for the user (for API token authentication)
            $user->tokens()->delete();

            // SECURITY FIX: Log successful logout with structured data
            Log::channel('security')->info('User logged out', [
                'event' => 'logout_success',
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return $this->success(null, 'Logged out');
    }
}
