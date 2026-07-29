<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.initial_admin_email', 'admin@gbsale.ru');
        $password = config('app.initial_admin_password', 'change-me');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Администратор',
                'phone' => null,
                'password' => Hash::make($password),
            ]
        );

        if (! $user->hasRole('superadmin')) {
            $user->assignRole('superadmin');
        }

        $this->command->info("Initial admin: {$email} / password from INITIAL_ADMIN_PASSWORD or 'change-me'");
    }
}
