<?php

namespace Database\Seeders;

use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class MataKuliahSeeder extends Seeder
{
    public function run(): void
    {
        $prodiTI = ProgramStudi::where('kode', 'TI')->first();
        $prodiSI = ProgramStudi::where('kode', 'SI')->first();

        $mataKuliah = [
            // Teknik Informatika
            ['kode' => 'TI101', 'nama' => 'Algoritma dan Pemrograman',    'sks' => 3, 'semester_anjuran' => 1, 'program_studi_id' => $prodiTI->id],
            ['kode' => 'TI102', 'nama' => 'Matematika Diskrit',           'sks' => 3, 'semester_anjuran' => 1, 'program_studi_id' => $prodiTI->id],
            ['kode' => 'TI201', 'nama' => 'Struktur Data',                'sks' => 3, 'semester_anjuran' => 3, 'program_studi_id' => $prodiTI->id],
            ['kode' => 'TI202', 'nama' => 'Basis Data',                   'sks' => 3, 'semester_anjuran' => 3, 'program_studi_id' => $prodiTI->id],
            ['kode' => 'TI301', 'nama' => 'Rekayasa Perangkat Lunak',     'sks' => 3, 'semester_anjuran' => 5, 'program_studi_id' => $prodiTI->id],
            ['kode' => 'TI302', 'nama' => 'Jaringan Komputer',            'sks' => 3, 'semester_anjuran' => 5, 'program_studi_id' => $prodiTI->id],
            ['kode' => 'TI401', 'nama' => 'Kecerdasan Buatan',            'sks' => 3, 'semester_anjuran' => 7, 'program_studi_id' => $prodiTI->id],
            // Sistem Informasi
            ['kode' => 'SI101', 'nama' => 'Pengantar Sistem Informasi',   'sks' => 3, 'semester_anjuran' => 1, 'program_studi_id' => $prodiSI->id],
            ['kode' => 'SI201', 'nama' => 'Analisis dan Desain Sistem',   'sks' => 3, 'semester_anjuran' => 3, 'program_studi_id' => $prodiSI->id],
            ['kode' => 'SI301', 'nama' => 'Manajemen Proyek TI',          'sks' => 3, 'semester_anjuran' => 5, 'program_studi_id' => $prodiSI->id],
        ];

        foreach ($mataKuliah as $mk) {
            MataKuliah::create(array_merge($mk, ['status' => 'Aktif']));
        }
    }
}
