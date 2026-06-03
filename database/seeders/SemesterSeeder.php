<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        // Semester lama (arsip)
        Semester::create([
            'nama'            => 'Semester Genap 2023/2024',
            'tahun_akademik'  => '2023/2024',
            'tipe'            => 'Genap',
            'tanggal_mulai'   => '2024-02-01',
            'tanggal_selesai' => '2024-07-31',
            'krs_buka'        => '2024-01-15',
            'krs_tutup'       => '2024-01-31',
            'nilai_buka'      => '2024-07-01',
            'nilai_tutup'     => '2024-07-31',
            'status'          => 'Arsip',
        ]);

        // Semester aktif sekarang
        Semester::create([
            'nama'            => 'Semester Ganjil 2024/2025',
            'tahun_akademik'  => '2024/2025',
            'tipe'            => 'Ganjil',
            'tanggal_mulai'   => '2024-09-01',
            'tanggal_selesai' => '2025-02-28',
            'krs_buka'        => '2024-08-01',
            'krs_tutup'       => '2024-08-31',
            'nilai_buka'      => '2025-01-15',
            'nilai_tutup'     => '2025-02-28',
            'status'          => 'Aktif',
        ]);
    }
}
