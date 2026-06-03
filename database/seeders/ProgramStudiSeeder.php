<?php

namespace Database\Seeders;

use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class ProgramStudiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'TI',  'nama' => 'Teknik Informatika',       'fakultas' => 'Fakultas Teknik',          'jenjang' => 'S1'],
            ['kode' => 'SI',  'nama' => 'Sistem Informasi',         'fakultas' => 'Fakultas Teknik',          'jenjang' => 'S1'],
            ['kode' => 'AK',  'nama' => 'Akuntansi',                'fakultas' => 'Fakultas Ekonomi',         'jenjang' => 'S1'],
            ['kode' => 'MN',  'nama' => 'Manajemen',                'fakultas' => 'Fakultas Ekonomi',         'jenjang' => 'S1'],
            ['kode' => 'HK',  'nama' => 'Ilmu Hukum',               'fakultas' => 'Fakultas Hukum',           'jenjang' => 'S1'],
        ];

        foreach ($data as $item) {
            ProgramStudi::create(array_merge($item, ['is_active' => true]));
        }
    }
}
