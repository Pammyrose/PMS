<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            IndicatorTypeSeeder::class,
            UsersSeeder::class,
            FieldsSeeder::class,
            EssentialsSeeder::class,
            TypeSeeder::class,
        ]);
    }
}