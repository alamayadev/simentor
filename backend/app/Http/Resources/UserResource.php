<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PERFORMANCE FIX: API Resource for User model
 *
 * This resource class provides a consistent, standardized API response structure
 * for User data. Using resources instead of returning models directly:
 * - Consists API responses across all endpoints
 * - Allows control over which fields are exposed
 * - Enables automatic data transformation and formatting
 * - Improves performance by avoiding N+1 queries from eager loading
 *
 * Usage:
 * Instead of: return $users;
 * Use: return UserResource::collection($users);
 *
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'current_team_id' => $this->current_team_id,
            'profile_photo_path' => $this->profile_photo_path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include roles if loaded to avoid N+1 queries
            'roles' => $this->whenLoaded('roles'),

            // Include permissions if loaded to avoid N+1 queries
            'permissions' => $this->whenLoaded('permissions'),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'meta' => [
                'resource_type' => 'user',
            ],
        ];
    }
}
