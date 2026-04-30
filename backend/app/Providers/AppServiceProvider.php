<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // Register model policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Tiket::class, \App\Policies\TiketPolicy::class);

        // Fix morph map for Spatie Permission
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'App\Models\User' => \App\Models\User::class,
        ]);
    }
}
