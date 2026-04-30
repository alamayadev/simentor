<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Fortify\TwoFactorAuthenticatable;
// use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    // use HasProfilePhoto;
    use Notifiable;
    // use TwoFactorAuthenticatable;
    use HasRoles {
        hasPermissionTo as spatieHasPermissionTo;
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        // 'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }
    public function pegawai(): HasOne
    {
        return $this->hasOne(Pegawai::class, 'user_id');
    }

    /**
     * Set the user's password and automatically hash it.
     *
     * @param string $password
     * @return void
     */
    public function setPasswordAttribute(string $password): void
    {
        $this->attributes['password'] = \Illuminate\Support\Facades\Hash::needsRehash($password) 
            ? bcrypt($password) 
            : $password;
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        try {
            return $this->spatieHasPermissionTo($permission, $guardName);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }
}
