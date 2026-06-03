<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProgramStudiSeeder::class,
            UserSeeder::class,
            SemesterSeeder::class,
            MataKuliahSeeder::class,
            KelasSeeder::class,
        ]);
    }
}
