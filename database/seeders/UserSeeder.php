<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('kashoes.super_admin.email');
        $password = config('kashoes.super_admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('Super admin tidak dibuat: isi KASHOES_SUPER_ADMIN_EMAIL dan KASHOES_SUPER_ADMIN_PASSWORD.');

            return;
        }

        $superAdmin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => config('kashoes.super_admin.name'),
                'password' => $password,
            ]
        );

        $superAdmin->assignRole('super_admin');

    }
}
