<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test user with super-admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $user->assignRole('super-admin');

        $this->info('Test user created successfully with ID: ' . $user->id);
        $this->info('Email: test@example.com');
        $this->info('Password: password');
    }
}
