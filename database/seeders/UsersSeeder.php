<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->upsert([
            [
                'name'   => 'Super Administrator',
                'email'  => 'superadmin@denr.gov.ph',
                'password' => Hash::make('password'),
                'role'  => 'super-admin',
            ],
            [
                'name' => 'Admin Admin',
                'email' => 'admin@denr.gov.ph',
                'password' => Hash::make('password'),
                'role'              => 'admin',
            ],
            [
                'name' => 'Cenro Cenro',
                'email' => 'cenro@denr.gov.ph',
                'password' => Hash::make('password'),
                'role'              => 'cenro',
            ],
            [
                'name' => 'Penro Penro',
                'email' => 'penro@denr.gov.ph',
                'password' => Hash::make('password'),
                'role'              => 'penro',
            ],
        ], ['email'], ['name', 'password', 'role']);
    }
}
