<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Admin;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Nilai;
use App\Models\Absensi;
use App\Models\Pembayaran;
use App\Models\Surat;
use App\Models\Pengumuman;
use App\Models\CatatanBimbingan;
use App\Models\Semester;
use App\Models\Ujian;
use App\Models\SoalUjian;
use App\Models\SesiUjian;
use Illuminate\Database\Seeder;

class AktivitasSeeder extends Seeder
{
    private function bobotDariNilai($nilai)
    {
        if ($nilai >= 85) return ['A', 4.0];
        if ($nilai >= 80) return ['A-', 3.7];
        if ($nilai >= 75) return ['B+', 3.3];
        if ($nilai >= 70) return ['B', 3.0];
        if ($nilai >= 65) return ['B-', 2.7];
        if ($nilai >= 60) return ['C+', 2.3];
        if ($nilai >= 55) return ['C', 2.0];
        return ['D', 1.0];
    }

    public function run(): void
    {
        $semesterUrut  = Semester::orderBy('tanggal_mulai')->get();
        $semesterAktif = Semester::where('status', 'Aktif')->first();
        $admin         = Admin::first();

        // ════════════════════════════════════════════════════
        // 1. RIWAYAT AKADEMIK — KRS + NILAI + ABSENSI
        //    untuk semua mahasiswa angkatan 2021 (semester 1-6 = riwayat, 7 = aktif)
        // ════════════════════════════════════════════════════
        $mahasiswaAngkatan2021 = Mahasiswa::where('angkatan', 2021)->get();

        foreach ($mahasiswaAngkatan2021 as $mhs) {
            // Riwayat semester 1-6 (semua sudah lulus dengan nilai bervariasi)
            for ($i = 0; $i < 6; $i++) {
                $semester = $semesterUrut[$i];
                $kelasSemester = Kelas::where('semester_id', $semester->id)->get();

                foreach ($kelasSemester as $kelas) {
                    $krs = Krs::create([
                        'mahasiswa_id' => $mhs->id,
                        'kelas_id'     => $kelas->id,
                        'semester_id'  => $semester->id,
                        'status'       => 'Disetujui',
                        'diajukan_at'  => $semester->krs_buka,
                        'diproses_at'  => $semester->krs_tutup,
                    ]);

                    // Nilai bervariasi tapi realistis (80-95 untuk mahasiswa rajin, dengan sedikit variasi acak)
                    $nilaiAkhir = rand(72, 95);
                    [$grade, $bobot] = $this->bobotDariNilai($nilaiAkhir);

                    Nilai::create([
                        'mahasiswa_id' => $mhs->id,
                        'kelas_id'     => $kelas->id,
                        'semester_id'  => $semester->id,
                        'nilai_tugas'  => min(100, $nilaiAkhir + rand(-5, 8)),
                        'nilai_uts'    => min(100, $nilaiAkhir + rand(-8, 5)),
                        'nilai_uas'    => min(100, $nilaiAkhir + rand(-5, 5)),
                        'nilai_akhir'  => $nilaiAkhir,
                        'grade'        => $grade,
                        'bobot'        => $bobot,
                        'status'       => 'Lulus',
                    ]);

                    // Absensi — rata-rata kehadiran baik (85-100%)
                    $totalPertemuan = 14;
                    $hadir = rand(12, 14);
                    $izin  = rand(0, 1);
                    $sakit = rand(0, 1);
                    $alpha = max(0, $totalPertemuan - $hadir - $izin - $sakit);

                    Absensi::create([
                        'mahasiswa_id'    => $mhs->id,
                        'kelas_id'        => $kelas->id,
                        'semester_id'     => $semester->id,
                        'total_pertemuan' => $totalPertemuan,
                        'hadir'           => $hadir,
                        'izin'            => $izin,
                        'sakit'           => $sakit,
                        'alpha'           => $alpha,
                        'persentase'      => round(($hadir / $totalPertemuan) * 100, 2),
                    ]);
                }

                // Pembayaran lunas untuk semester riwayat
                Pembayaran::create([
                    'mahasiswa_id'      => $mhs->id,
                    'semester_id'       => $semester->id,
                    'nominal'           => 3500000,
                    'status'            => 'Lunas',
                    'tanggal_bayar'     => $semester->krs_tutup,
                    'no_referensi'      => 'TRX-' . strtoupper(substr(md5($mhs->id . $semester->id), 0, 10)),
                    'dikonfirmasi_oleh' => $admin?->id,
                ]);
            }

            // Semester 7 (aktif) — KRS diambil, nilai BELUM ada (sedang berjalan), absensi sebagian
            $kelasAktif = Kelas::where('semester_id', $semesterAktif->id)->get();
            foreach ($kelasAktif as $kelas) {
                Krs::create([
                    'mahasiswa_id' => $mhs->id,
                    'kelas_id'     => $kelas->id,
                    'semester_id'  => $semesterAktif->id,
                    'status'       => 'Disetujui',
                    'diajukan_at'  => $semesterAktif->krs_buka,
                    'diproses_at'  => $semesterAktif->krs_tutup,
                ]);

                // Absensi sedang berjalan — baru beberapa pertemuan
                $totalPertemuan = rand(5, 8);
                $hadir = rand(4, $totalPertemuan);
                Absensi::create([
                    'mahasiswa_id'    => $mhs->id,
                    'kelas_id'        => $kelas->id,
                    'semester_id'     => $semesterAktif->id,
                    'total_pertemuan' => $totalPertemuan,
                    'hadir'           => $hadir,
                    'izin'            => 0,
                    'sakit'           => $totalPertemuan - $hadir,
                    'alpha'           => 0,
                    'persentase'      => round(($hadir / $totalPertemuan) * 100, 2),
                ]);
            }

            // Pembayaran semester aktif — menunggak (realistis untuk simulasi testing fitur bayar)
            Pembayaran::create([
                'mahasiswa_id' => $mhs->id,
                'semester_id'  => $semesterAktif->id,
                'nominal'      => 3500000,
                'status'       => 'Menunggak',
            ]);
        }

        // Mahasiswa Bima Pratama (semester 5, SI) dan Reza (semester 3, TI) — riwayat lebih singkat
        $mhsLain = Mahasiswa::whereIn('nim', ['2022001', '2023001'])->get();
        foreach ($mhsLain as $mhs) {
            $jmlSemesterLulus = $mhs->semester - 1;
            for ($i = 0; $i < $jmlSemesterLulus && $i < 6; $i++) {
                $semester = $semesterUrut[$i];
                $kelasSemester = Kelas::where('semester_id', $semester->id)
                    ->whereHas('mataKuliah', fn($q) => $q->where('program_studi_id', $mhs->program_studi_id))
                    ->get();

                foreach ($kelasSemester as $kelas) {
                    Krs::create([
                        'mahasiswa_id' => $mhs->id, 'kelas_id' => $kelas->id, 'semester_id' => $semester->id,
                        'status' => 'Disetujui', 'diajukan_at' => $semester->krs_buka, 'diproses_at' => $semester->krs_tutup,
                    ]);
                    $nilaiAkhir = rand(70, 92);
                    [$grade, $bobot] = $this->bobotDariNilai($nilaiAkhir);
                    Nilai::create([
                        'mahasiswa_id' => $mhs->id, 'kelas_id' => $kelas->id, 'semester_id' => $semester->id,
                        'nilai_tugas' => $nilaiAkhir, 'nilai_uts' => $nilaiAkhir, 'nilai_uas' => $nilaiAkhir,
                        'nilai_akhir' => $nilaiAkhir, 'grade' => $grade, 'bobot' => $bobot, 'status' => 'Lulus',
                    ]);
                }
                Pembayaran::create([
                    'mahasiswa_id' => $mhs->id, 'semester_id' => $semester->id,
                    'nominal' => 3500000, 'status' => 'Lunas',
                    'tanggal_bayar' => $semester->krs_tutup,
                    'no_referensi' => 'TRX-' . strtoupper(substr(md5($mhs->id . $semester->id), 0, 10)),
                    'dikonfirmasi_oleh' => $admin?->id,
                ]);
            }

            // KRS aktif sedang menunggu persetujuan dosen (untuk testing panel persetujuan KRS dosen)
            $kelasAktifProdi = Kelas::where('semester_id', $semesterAktif->id)
                ->whereHas('mataKuliah', fn($q) => $q->where('program_studi_id', $mhs->program_studi_id))
                ->get();
            foreach ($kelasAktifProdi as $kelas) {
                Krs::create([
                    'mahasiswa_id' => $mhs->id, 'kelas_id' => $kelas->id, 'semester_id' => $semesterAktif->id,
                    'status' => 'Menunggu', 'diajukan_at' => now(),
                ]);
            }
        }

        // ════════════════════════════════════════════════════
        // 2. SURAT — beberapa pengajuan dengan status bervariasi
        // ════════════════════════════════════════════════════
        $rizky = Mahasiswa::where('nim', '2021001')->first();

        $surat = [
            ['jenis' => 'Surat Keterangan Mahasiswa Aktif', 'keperluan' => 'Persyaratan beasiswa', 'status' => 'Selesai', 'hari' => -20],
            ['jenis' => 'Surat Keterangan IPK',             'keperluan' => 'Lamaran kerja part-time', 'status' => 'Selesai', 'hari' => -10],
            ['jenis' => 'Surat Rekomendasi',                'keperluan' => 'Pendaftaran organisasi', 'status' => 'Diproses', 'hari' => -3],
            ['jenis' => 'Surat Keterangan Mahasiswa Aktif', 'keperluan' => 'Pengajuan KTP sementara', 'status' => 'Menunggu', 'hari' => -1],
        ];

        foreach ($surat as $i => $s) {
            $tgl = now()->addDays($s['hari']);
            Surat::create([
                'no_pengajuan'  => 'SRT-' . strtoupper(uniqid()) . '-' . $tgl->format('Ymd'),
                'mahasiswa_id'  => $rizky->id,
                'jenis_surat'   => $s['jenis'],
                'keperluan'     => $s['keperluan'],
                'status'        => $s['status'],
                'diproses_oleh' => $s['status'] !== 'Menunggu' ? $admin?->id : null,
                'diproses_at'   => $s['status'] !== 'Menunggu' ? $tgl : null,
                'created_at'    => $tgl,
                'updated_at'    => $tgl,
            ]);
        }

        // ════════════════════════════════════════════════════
        // 3. PENGUMUMAN
        // ════════════════════════════════════════════════════
        $pengumuman = [
            ['judul' => 'Jadwal UTS Semester Ganjil 2024/2025', 'isi' => 'UTS akan dilaksanakan mulai 4 November 2024. Pastikan kehadiran minimal 75% di setiap mata kuliah.', 'penting' => true],
            ['judul' => 'Pembukaan Pendaftaran Beasiswa PPA', 'isi' => 'Pendaftaran beasiswa PPA dibuka mulai hari ini hingga akhir bulan. Hubungi bagian akademik untuk info lebih lanjut.', 'penting' => true],
            ['judul' => 'Libur Nasional — Kampus Tutup', 'isi' => 'Kampus akan tutup pada tanggal libur nasional. Aktivitas akademik akan kembali normal pada hari kerja berikutnya.', 'penting' => false],
            ['judul' => 'Pemeliharaan Sistem SIAKAD', 'isi' => 'Sistem akan mengalami pemeliharaan rutin pada akhir pekan. Mohon maaf atas ketidaknyamanannya.', 'penting' => false],
        ];

        foreach ($pengumuman as $i => $p) {
            Pengumuman::create([
                'judul'       => $p['judul'],
                'isi'         => $p['isi'],
                'kategori'    => 'Akademik',
                'target'      => 'Semua',
                'penting'     => $p['penting'],
                'baru'        => $i === 0,
                'status'      => 'Aktif',
                'dibuat_oleh' => $admin?->id,
                'created_at'  => now()->subDays($i * 3),
                'updated_at'  => now()->subDays($i * 3),
            ]);
        }

        // ════════════════════════════════════════════════════
        // 4. CATATAN BIMBINGAN PA
        // ════════════════════════════════════════════════════
        $dosen1 = Dosen::where('nip', '198501012010011001')->first();

        CatatanBimbingan::create([
            'mahasiswa_id'  => $rizky->id,
            'dosen_id'      => $dosen1->id,
            'topik'         => 'Konsultasi Rencana Studi Semester 7',
            'catatan'       => 'Mahasiswa berkonsultasi mengenai pengambilan mata kuliah pilihan untuk semester akhir. Disarankan fokus pada mata kuliah yang relevan dengan topik skripsi.',
            'tindak_lanjut' => 'Mahasiswa akan menentukan topik skripsi dalam 2 minggu ke depan.',
        ]);

        CatatanBimbingan::create([
            'mahasiswa_id'  => $rizky->id,
            'dosen_id'      => $dosen1->id,
            'topik'         => 'Progress Pengerjaan Tugas Akhir',
            'catatan'       => 'Mahasiswa melaporkan progress 30% pada bab pendahuluan. Perlu perbaikan pada rumusan masalah.',
            'tindak_lanjut' => 'Revisi bab 1 dikumpulkan minggu depan.',
        ]);

        // ════════════════════════════════════════════════════
        // 5. UJIAN — termasuk satu yang sudah Selesai (untuk testing pembahasan)
        //    dan satu yang masih Berlangsung
        // ════════════════════════════════════════════════════
        $kelasRizky = Kelas::where('semester_id', $semesterAktif->id)->first();

        $ujianSelesai = Ujian::create([
            'nama'            => 'Kuis Kecerdasan Buatan',
            'kelas_id'        => $kelasRizky->id,
            'dosen_id'        => $kelasRizky->dosen_id,
            'semester_id'     => $semesterAktif->id,
            'tipe'            => 'Kuis',
            'durasi'          => 30,
            'mulai_at'        => now()->subDays(5),
            'selesai_at'      => now()->subDays(5)->addMinutes(30),
            'status'          => 'Selesai',
            'max_pelanggaran' => 3,
        ]);

        $soalData = [
            ['pertanyaan' => 'Apa kepanjangan dari AI dalam konteks ilmu komputer?', 'pilihan' => ['A' => 'Artificial Intelligence', 'B' => 'Automated Interface', 'C' => 'Advanced Integration', 'D' => 'Algorithmic Iteration'], 'jawaban_benar' => 'A'],
            ['pertanyaan' => 'Algoritma machine learning yang meniru cara kerja otak manusia disebut?', 'pilihan' => ['A' => 'Decision Tree', 'B' => 'Neural Network', 'C' => 'K-Means', 'D' => 'Linear Regression'], 'jawaban_benar' => 'B'],
            ['pertanyaan' => 'Proses melatih model dengan data berlabel disebut?', 'pilihan' => ['A' => 'Unsupervised Learning', 'B' => 'Reinforcement Learning', 'C' => 'Supervised Learning', 'D' => 'Transfer Learning'], 'jawaban_benar' => 'C'],
        ];

        $soalIds = [];
        foreach ($soalData as $i => $s) {
            $soal = SoalUjian::create([
                'ujian_id'      => $ujianSelesai->id,
                'nomor'         => $i + 1,
                'pertanyaan'    => $s['pertanyaan'],
                'tipe'          => 'pilihan_ganda',
                'pilihan'       => collect($s['pilihan'])->map(fn($teks, $key) => ['key' => $key, 'teks' => $teks])->values()->toArray(),
                'jawaban_benar' => $s['jawaban_benar'],
                'bobot'         => 1,
            ]);
            $soalIds[] = $soal;
        }

        SesiUjian::create([
            'ujian_id'     => $ujianSelesai->id,
            'mahasiswa_id' => $rizky->id,
            'mulai_at'     => now()->subDays(5),
            'selesai_at'   => now()->subDays(5)->addMinutes(25),
            'nilai'        => 87,
            'status'       => 'Selesai',
            'pelanggaran'  => 0,
        ]);

        // Ujian berlangsung (untuk testing panel monitoring real-time admin & dosen)
        $kelasKedua = Kelas::where('semester_id', $semesterAktif->id)->skip(1)->first();
        Ujian::create([
            'nama'            => 'UTS Keamanan Sistem Informasi',
            'kelas_id'        => $kelasKedua?->id ?? $kelasRizky->id,
            'dosen_id'        => $kelasKedua?->dosen_id ?? $kelasRizky->dosen_id,
            'semester_id'     => $semesterAktif->id,
            'tipe'            => 'UTS',
            'durasi'          => 90,
            'mulai_at'        => now()->subMinutes(20),
            'selesai_at'      => now()->addMinutes(70),
            'status'          => 'Berlangsung',
            'max_pelanggaran' => 3,
        ]);
    }
}
