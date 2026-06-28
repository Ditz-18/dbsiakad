<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── ADMIN ──────────────────────────────────────────
        $adminUser = User::create([
            'username'  => 'admin01',
            'email'     => 'admin@siakad.com',
            'password'  => 'admin123',
            'role'      => 'admin',
            'is_active' => true,
        ]);
        Admin::create([
            'user_id'   => $adminUser->id,
            'nama'      => 'Adit',
            'jabatan'   => 'Admin Akademik',
            'no_hp'     => '08123456789',
            'is_active' => true,
        ]);

        // ── DOSEN ──────────────────────────────────────────
        $prodiTI = ProgramStudi::where('kode', 'TI')->first();
        $prodiSI = ProgramStudi::where('kode', 'SI')->first();

        $dosen1User = User::create([
            'username' => 'dosen01', 'email' => 'dosen01@siakad.com',
            'password' => 'dosen123', 'role' => 'dosen', 'is_active' => true,
        ]);
        $dosen1 = Dosen::create([
            'user_id' => $dosen1User->id, 'nip' => '198501012010011001',
            'nama' => 'Dr. Budi Santoso, M.Kom', 'program_studi_id' => $prodiTI->id,
            'fakultas' => 'Fakultas Teknik', 'jabatan' => 'Lektor', 'golongan' => 'III/c',
            'email_akademik' => 'budi.santoso@univ.ac.id', 'no_hp' => '08111111111', 'is_active' => true,
        ]);

        $dosen2User = User::create([
            'username' => 'dosen02', 'email' => 'dosen02@siakad.com',
            'password' => 'dosen123', 'role' => 'dosen', 'is_active' => true,
        ]);
        $dosen2 = Dosen::create([
            'user_id' => $dosen2User->id, 'nip' => '199002022015042002',
            'nama' => 'Sari Dewi, M.T', 'program_studi_id' => $prodiSI->id,
            'fakultas' => 'Fakultas Teknik', 'jabatan' => 'Asisten Ahli', 'golongan' => 'III/b',
            'email_akademik' => 'sari.dewi@univ.ac.id', 'no_hp' => '08122222222', 'is_active' => true,
        ]);

        $dosen3User = User::create([
            'username' => 'dosen03', 'email' => 'dosen03@siakad.com',
            'password' => 'dosen123', 'role' => 'dosen', 'is_active' => true,
        ]);
        $dosen3 = Dosen::create([
            'user_id' => $dosen3User->id, 'nip' => '198803152012031003',
            'nama' => 'Rina Wijayanti, M.Kom', 'program_studi_id' => $prodiTI->id,
            'fakultas' => 'Fakultas Teknik', 'jabatan' => 'Lektor Kepala', 'golongan' => 'IV/a',
            'email_akademik' => 'rina.wijayanti@univ.ac.id', 'no_hp' => '08177777777', 'is_active' => true,
        ]);

        $dosen4User = User::create([
            'username' => 'dosen04', 'email' => 'dosen04@siakad.com',
            'password' => 'dosen123', 'role' => 'dosen', 'is_active' => true,
        ]);
        $dosen4 = Dosen::create([
            'user_id' => $dosen4User->id, 'nip' => '199105202016071004',
            'nama' => 'Fajar Nugroho, M.Kom', 'program_studi_id' => $prodiTI->id,
            'fakultas' => 'Fakultas Teknik', 'jabatan' => 'Asisten Ahli', 'golongan' => 'III/a',
            'email_akademik' => 'fajar.nugroho@univ.ac.id', 'no_hp' => '08188888888', 'is_active' => true,
        ]);

        // ── MAHASISWA ──────────────────────────────────────
        $mhs1User = User::create([
            'username' => 'mhs2021001', 'email' => 'mhs001@siakad.com',
            'password' => 'mhs123', 'role' => 'mahasiswa', 'is_active' => true,
        ]);
        $mhs1 = Mahasiswa::create([
            'user_id' => $mhs1User->id, 'nim' => '2021001', 'nama' => 'Ahmad Rizky',
            'program_studi_id' => $prodiTI->id, 'angkatan' => 2021, 'semester' => 7,
            'status' => 'Aktif', 'dosen_pa_id' => $dosen1->id,
            'email' => 'ahmad.rizky@gmail.com', 'no_hp' => '08133333333',
            'alamat' => 'Jl. Merdeka No. 1, Jakarta',
        ]);

        $mhs2User = User::create([
            'username' => 'mhs2021002', 'email' => 'mhs002@siakad.com',
            'password' => 'mhs123', 'role' => 'mahasiswa', 'is_active' => true,
        ]);
        $mhs2 = Mahasiswa::create([
            'user_id' => $mhs2User->id, 'nim' => '2021002', 'nama' => 'Siti Rahma',
            'program_studi_id' => $prodiTI->id, 'angkatan' => 2021, 'semester' => 7,
            'status' => 'Aktif', 'dosen_pa_id' => $dosen1->id,
            'email' => 'siti.rahma@gmail.com', 'no_hp' => '08144444444',
            'alamat' => 'Jl. Sudirman No. 5, Jakarta',
        ]);

        $mhs3User = User::create([
            'username' => 'mhs2022001', 'email' => 'mhs003@siakad.com',
            'password' => 'mhs123', 'role' => 'mahasiswa', 'is_active' => true,
        ]);
        $mhs3 = Mahasiswa::create([
            'user_id' => $mhs3User->id, 'nim' => '2022001', 'nama' => 'Bima Pratama',
            'program_studi_id' => $prodiSI->id, 'angkatan' => 2022, 'semester' => 5,
            'status' => 'Aktif', 'dosen_pa_id' => $dosen2->id,
            'email' => 'bima.pratama@gmail.com', 'no_hp' => '08155555555',
            'alamat' => 'Jl. Gatot Subroto No. 10, Jakarta',
        ]);

        $mhs4User = User::create([
            'username' => 'mhs2021003', 'email' => 'mhs004@siakad.com',
            'password' => 'mhs123', 'role' => 'mahasiswa', 'is_active' => true,
        ]);
        Mahasiswa::create([
            'user_id' => $mhs4User->id, 'nim' => '2021003', 'nama' => 'Dimas Anggara',
            'program_studi_id' => $prodiTI->id, 'angkatan' => 2021, 'semester' => 7,
            'status' => 'Aktif', 'dosen_pa_id' => $dosen3->id,
            'email' => 'dimas.anggara@gmail.com', 'no_hp' => '08166666666',
            'alamat' => 'Jl. Diponegoro No. 8, Jakarta',
        ]);

        $mhs5User = User::create([
            'username' => 'mhs2021004', 'email' => 'mhs005@siakad.com',
            'password' => 'mhs123', 'role' => 'mahasiswa', 'is_active' => true,
        ]);
        Mahasiswa::create([
            'user_id' => $mhs5User->id, 'nim' => '2021004', 'nama' => 'Putri Lestari',
            'program_studi_id' => $prodiTI->id, 'angkatan' => 2021, 'semester' => 7,
            'status' => 'Aktif', 'dosen_pa_id' => $dosen3->id,
            'email' => 'putri.lestari@gmail.com', 'no_hp' => '08199999999',
            'alamat' => 'Jl. Pahlawan No. 3, Jakarta',
        ]);

        $mhs6User = User::create([
            'username' => 'mhs2023001', 'email' => 'mhs006@siakad.com',
            'password' => 'mhs123', 'role' => 'mahasiswa', 'is_active' => true,
        ]);
        Mahasiswa::create([
            'user_id' => $mhs6User->id, 'nim' => '2023001', 'nama' => 'Reza Firmansyah',
            'program_studi_id' => $prodiTI->id, 'angkatan' => 2023, 'semester' => 3,
            'status' => 'Aktif', 'dosen_pa_id' => $dosen4->id,
            'email' => 'reza.firmansyah@gmail.com', 'no_hp' => '08110101010',
            'alamat' => 'Jl. Veteran No. 12, Jakarta',
        ]);
    }
}
