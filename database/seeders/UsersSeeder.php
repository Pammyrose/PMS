<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
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
                'name' => 'User User',
                'email' => 'user@denr.gov.ph',
                'password' => Hash::make('password'),
                'role'              => 'user',
            ],
        ]);
    }
}
