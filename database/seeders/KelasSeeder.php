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
        $dosen1 = Dosen::where('nip', '198501012010011001')->first(); // Budi - TI ganjil
        $dosen2 = Dosen::where('nip', '199002022015042002')->first(); // Sari - SI
        $dosen3 = Dosen::where('nip', '198803152012031003')->first(); // Rina - TI genap
        $dosen4 = Dosen::where('nip', '199105202016071004')->first(); // Fajar - TI genap

        // Semester diurutkan dari yang paling lama ke yang terbaru (index 0 = semester 1 mahasiswa 2021)
        $semesterUrut = Semester::orderBy('tanggal_mulai')->get();

        $ruangan = ['Lab Komputer 1', 'Lab Komputer 2', 'Ruang 201', 'Ruang 202', 'Ruang 301', 'Ruang 302'];
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jamList  = [['08:00','10:30'], ['10:30','13:00'], ['13:00','15:30']];

        // Mapping semester_anjuran (1-7) ke index semester yang sesuai di $semesterUrut
        // semester_anjuran 1 -> semesterUrut[0], dst.
        $mkList = MataKuliah::all();
        $counter = 0;

        foreach ($mkList as $mk) {
            $semesterIndex = $mk->semester_anjuran - 1;
            if (!isset($semesterUrut[$semesterIndex])) continue;

            $semester = $semesterUrut[$semesterIndex];

            // Pilih dosen pengampu: TI ganjil -> dosen1, TI genap -> dosen3/dosen4 berseling, SI -> dosen2
            if (str_starts_with($mk->kode, 'SI')) {
                $dosen = $dosen2;
            } elseif ($mk->semester_anjuran % 2 === 1) {
                $dosen = $dosen1;
            } else {
                $dosen = $counter % 2 === 0 ? $dosen3 : $dosen4;
            }

            Kelas::create([
                'kode_kelas'     => $mk->kode . '-A',
                'mata_kuliah_id' => $mk->id,
                'dosen_id'       => $dosen->id,
                'semester_id'    => $semester->id,
                'ruangan'        => $ruangan[$counter % count($ruangan)],
                'hari'           => $hariList[$counter % count($hariList)],
                'jam_mulai'      => $jamList[$counter % count($jamList)][0],
                'jam_selesai'    => $jamList[$counter % count($jamList)][1],
                'kuota'          => 40,
                'is_active'      => true,
            ]);

            $counter++;
        }
    }
}
