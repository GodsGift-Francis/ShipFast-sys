<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed a super admin so the portal is reachable. Override via env.
        $email = env('ADMIN_EMAIL', 'admin@shipfast.local');
        $password = env('ADMIN_PASSWORD', 'admin12345');

        User::updateOrCreate(
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
