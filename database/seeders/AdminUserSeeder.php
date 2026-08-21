<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'changeme');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Pati',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );
    }
}
