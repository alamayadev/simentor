<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tiket;

class TiketPolicy
{
    /**
     * Determine whether the user can update the tiket.
     */
    public function update(User $user, Tiket $tiket): bool
    {
        // deny if user has role 'mitra' or 'user'
        if ($user->hasRole('mitra') || $user->hasRole('user')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the tiket.
     */
    public function delete(User $user, Tiket $tiket): bool
    {
        if ($user->hasRole('mitra') || $user->hasRole('user')) {
            return false;
        }

        return true;
    }
}
