<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // buat role
        foreach (['superadmin', 'admin', 'keuangan'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // superadmin
        $super = User::updateOrCreate(
            ['email' => 'admin-sdm@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $super->assignRole('superadmin');

        // keuangan
        $keu = User::updateOrCreate(
            ['email' => 'keu-sdm@gmail.com'],
            [
                'name' => 'Keuangan',
                'password' => Hash::make('password'),
            ]
        );
        $keu->assignRole('keuangan');
    }
}
