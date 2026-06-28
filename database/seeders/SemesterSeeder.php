<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        // Riwayat semester (arsip) — disusun supaya mahasiswa angkatan 2021
        // punya 6 semester riwayat + 1 semester aktif (semester 7)
        $riwayat = [
            ['nama' => 'Semester Ganjil 2021/2022', 'tahun' => '2021/2022', 'tipe' => 'Ganjil', 'mulai' => '2021-09-01', 'selesai' => '2022-02-28'],
            ['nama' => 'Semester Genap 2021/2022',  'tahun' => '2021/2022', 'tipe' => 'Genap',  'mulai' => '2022-03-01', 'selesai' => '2022-08-31'],
            ['nama' => 'Semester Ganjil 2022/2023', 'tahun' => '2022/2023', 'tipe' => 'Ganjil', 'mulai' => '2022-09-01', 'selesai' => '2023-02-28'],
            ['nama' => 'Semester Genap 2022/2023',  'tahun' => '2022/2023', 'tipe' => 'Genap',  'mulai' => '2023-03-01', 'selesai' => '2023-08-31'],
            ['nama' => 'Semester Ganjil 2023/2024', 'tahun' => '2023/2024', 'tipe' => 'Ganjil', 'mulai' => '2023-09-01', 'selesai' => '2024-02-28'],
            ['nama' => 'Semester Genap 2023/2024',  'tahun' => '2023/2024', 'tipe' => 'Genap',  'mulai' => '2024-03-01', 'selesai' => '2024-08-31'],
        ];

        foreach ($riwayat as $s) {
            Semester::create([
                'nama'            => $s['nama'],
                'tahun_akademik'  => $s['tahun'],
                'tipe'            => $s['tipe'],
                'tanggal_mulai'   => $s['mulai'],
                'tanggal_selesai' => $s['selesai'],
                'krs_buka'        => date('Y-m-d', strtotime($s['mulai'] . ' -3 weeks')),
                'krs_tutup'       => date('Y-m-d', strtotime($s['mulai'] . ' -1 week')),
                'nilai_buka'      => date('Y-m-d', strtotime($s['selesai'] . ' -3 weeks')),
                'nilai_tutup'     => $s['selesai'],
                'status'          => 'Arsip',
            ]);
        }

        // Semester aktif sekarang (semester 7 mahasiswa angkatan 2021)
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
