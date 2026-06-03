<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $semesterAktif = Semester::where('status', 'Aktif')->first();
        $dosen1        = Dosen::where('nip', '198501012010011001')->first();
        $dosen2        = Dosen::where('nip', '199002022015042002')->first();

        $kelas = [
            [
                'kode_kelas'      => 'TI401-A',
                'mata_kuliah_id'  => MataKuliah::where('kode', 'TI401')->first()->id,
                'dosen_id'        => $dosen1->id,
                'semester_id'     => $semesterAktif->id,
                'ruangan'         => 'Lab Komputer 1',
                'hari'            => 'Senin',
                'jam_mulai'       => '08:00',
                'jam_selesai'     => '10:30',
                'kuota'           => 35,
            ],
            [
                'kode_kelas'      => 'TI301-A',
                'mata_kuliah_id'  => MataKuliah::where('kode', 'TI301')->first()->id,
                'dosen_id'        => $dosen1->id,
                'semester_id'     => $semesterAktif->id,
                'ruangan'         => 'Ruang 201',
                'hari'            => 'Rabu',
                'jam_mulai'       => '10:00',
                'jam_selesai'     => '12:30',
                'kuota'           => 40,
            ],
            [
                'kode_kelas'      => 'SI301-A',
                'mata_kuliah_id'  => MataKuliah::where('kode', 'SI301')->first()->id,
                'dosen_id'        => $dosen2->id,
                'semester_id'     => $semesterAktif->id,
                'ruangan'         => 'Ruang 301',
                'hari'            => 'Selasa',
                'jam_mulai'       => '13:00',
                'jam_selesai'     => '15:30',
                'kuota'           => 40,
            ],
            [
                'kode_kelas'      => 'SI201-A',
                'mata_kuliah_id'  => MataKuliah::where('kode', 'SI201')->first()->id,
                'dosen_id'        => $dosen2->id,
                'semester_id'     => $semesterAktif->id,
                'ruangan'         => 'Ruang 302',
                'hari'            => 'Kamis',
                'jam_mulai'       => '08:00',
                'jam_selesai'     => '10:30',
                'kuota'           => 40,
            ],
        ];

        foreach ($kelas as $k) {
            Kelas::create(array_merge($k, ['is_active' => true]));
        }
    }
}
