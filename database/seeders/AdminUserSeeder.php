<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role 'admin' jika belum ada
        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        // Buat role 'user' juga jika perlu
        Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        // Buat akun admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Assign role admin ke user tersebut
        $admin->assignRole('admin');
    }
}
