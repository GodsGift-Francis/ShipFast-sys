<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Use shipfast:provision-admin to create the hosted administrator privately.');
        }
        // Seed a super admin so the portal is reachable. Override via env.
        $email = env('ADMIN_EMAIL', 'admin@shipfast.local');
        $password = env('ADMIN_PASSWORD', 'admin12345');

        User::firstOrCreate(
            ['email' => $email],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'status' => 'active',
            ],
        );
    }
}
