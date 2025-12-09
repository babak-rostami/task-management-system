<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class createAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create An Admin User';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->ask('Enter admin email:');
        $password = $this->secret('Enter admin password:');

        $user = User::where('email', $email)->first();

        // Update existing user role
        if ($user) {

            if ($user->hasRole('admin')) {
                $this->error('This user is already an admin.');
                return 1;
            }

            // Remove user role if exists
            if ($user->hasRole('user')) {
                $user->removeRole('user');
            }

            $user->assignRole('admin');

            $this->info("Success: The user ({$user->email}) has been promoted to admin.");
            return 0;
        }

        // Create new admin user
        $user = User::factory()
            ->admin()
            ->create([
                'email' => $email,
                'password' => Hash::make($password),
            ]);

        $this->info("Success: A new admin account has been created، ({$user->email}).");

        return 0;
    }

}
