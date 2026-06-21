<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;

// Mahasiswa
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboard;
use App\Http\Controllers\Mahasiswa\ProfilController;
use App\Http\Controllers\Mahasiswa\KrsController as MahasiswaKrs;
use App\Http\Controllers\Mahasiswa\KhsController;
use App\Http\Controllers\Mahasiswa\JadwalController;
use App\Http\Controllers\Mahasiswa\AbsensiController as MahasiswaAbsensi;
use App\Http\Controllers\Mahasiswa\PembayaranController as MahasiswaPembayaran;
use App\Http\Controllers\Mahasiswa\DokumenController;
use App\Http\Controllers\Mahasiswa\UjianController as MahasiswaUjian;
use App\Http\Controllers\Mahasiswa\PengumumanController as MahasiswaPengumuman;
use App\Http\Controllers\Mahasiswa\KtmController as MahasiswaKtm;

// Dosen
use App\Http\Controllers\Dosen\DashboardController as DosenDashboard;
use App\Http\Controllers\Dosen\ProfilController as DosenProfil;
use App\Http\Controllers\Dosen\KelasController;
use App\Http\Controllers\Dosen\NilaiController;
use App\Http\Controllers\Dosen\AbsensiController as DosenAbsensi;
use App\Http\Controllers\Dosen\BimbinganController;
use App\Http\Controllers\Dosen\KrsPersetujuanController;
use App\Http\Controllers\Dosen\UjianController as DosenUjian;

// Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\DosenController;
use App\Http\Controllers\Admin\MataKuliahController;
use App\Http\Controllers\Admin\KelasController as AdminKelas;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaran;
use App\Http\Controllers\Admin\SuratController;
use App\Http\Controllers\Admin\PengumumanController as AdminPengumuman;
use App\Http\Controllers\Admin\ProgramStudiController;
use App\Http\Controllers\Admin\KtmController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\UjianController as AdminUjian;

// ─────────────────────────────────────────────
// PUBLIC ROUTES
// ─────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',           [LoginController::class, 'login']);
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('reset-password',  [PasswordResetController::class, 'reset']);
});

// ─────────────────────────────────────────────
// PROTECTED ROUTES
// ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [LoginController::class, 'logout']);
    Route::get('auth/me',      [LoginController::class, 'me']);

    // ── MAHASISWA ──────────────────────────────
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->group(function () {
        Route::get('dashboard', [MahasiswaDashboard::class, 'index']);

        Route::get('profil',           [ProfilController::class, 'show']);
        Route::put('profil',           [ProfilController::class, 'update']);
        Route::put('profil/password',  [ProfilController::class, 'gantiPassword']);
        Route::post('profil/foto',     [ProfilController::class, 'uploadFoto']);

        Route::get('krs',         [MahasiswaKrs::class, 'index']);
        Route::post('krs',        [MahasiswaKrs::class, 'store']);
        Route::delete('krs/{id}', [MahasiswaKrs::class, 'destroy']);

        Route::get('khs',      [KhsController::class, 'index']);
        Route::get('jadwal',   [JadwalController::class, 'index']);
        Route::get('absensi',  [MahasiswaAbsensi::class, 'index']);

        Route::get('pembayaran',  [MahasiswaPembayaran::class, 'index']);
        Route::post('pembayaran', [MahasiswaPembayaran::class, 'store']);

        Route::get('dokumen',       [DokumenController::class, 'index']);
        Route::post('dokumen',      [DokumenController::class, 'store']);
        Route::get('dokumen/{id}',  [DokumenController::class, 'show']);

        // Ujian
        Route::get('ujian',                       [MahasiswaUjian::class, 'index']);
        Route::get('ujian/{id}',                  [MahasiswaUjian::class, 'show']);
        Route::get('ujian/{id}/soal',             [MahasiswaUjian::class, 'soal']);
        Route::post('ujian/{id}/jawab',           [MahasiswaUjian::class, 'jawab']);
        Route::post('ujian/{id}/submit',          [MahasiswaUjian::class, 'submit']);
        Route::post('ujian/{id}/pelanggaran',     [MahasiswaUjian::class, 'pelanggaran']);
        Route::get('ujian/{id}/pembahasan',       [MahasiswaUjian::class, 'pembahasan']);

        Route::get('pengumuman',      [MahasiswaPengumuman::class, 'index']);
        Route::get('pengumuman/{id}', [MahasiswaPengumuman::class, 'show']);

        Route::get('ktm', [MahasiswaKtm::class, 'show']);
    });

    // ── DOSEN ──────────────────────────────────
    Route::middleware('role:dosen')->prefix('dosen')->group(function () {
        Route::get('dashboard', [DosenDashboard::class, 'index']);

        Route::get('profil',          [DosenProfil::class, 'show']);
        Route::put('profil',          [DosenProfil::class, 'update']);
        Route::put('profil/password', [DosenProfil::class, 'gantiPassword']);
        Route::post('profil/foto',    [DosenProfil::class, 'uploadFoto']);

        Route::get('kelas',      [KelasController::class, 'index']);
        Route::get('kelas/{id}', [KelasController::class, 'show']);

        Route::get('kelas/{kelasId}/nilai',       [NilaiController::class, 'index']);
        Route::post('kelas/{kelasId}/nilai',      [NilaiController::class, 'store']);
        Route::put('kelas/{kelasId}/nilai/{id}',  [NilaiController::class, 'update']);

        Route::get('kelas/{kelasId}/absensi',     [DosenAbsensi::class, 'index']);
        Route::post('kelas/{kelasId}/absensi/catat-pertemuan', [DosenAbsensi::class, 'catatPertemuan']);
        Route::put('kelas/{kelasId}/absensi/{id}',[DosenAbsensi::class, 'update']);

        Route::get('bimbingan',                          [BimbinganController::class, 'index']);
        Route::get('bimbingan/{mahasiswaId}',            [BimbinganController::class, 'show']);
        Route::post('bimbingan/{mahasiswaId}/catatan',   [BimbinganController::class, 'storeCatatan']);

        Route::get('krs',              [KrsPersetujuanController::class, 'index']);
        Route::put('krs/{id}/setujui', [KrsPersetujuanController::class, 'setujui']);
        Route::put('krs/{id}/tolak',   [KrsPersetujuanController::class, 'tolak']);

        // Ujian + Soal
        Route::get('ujian',                              [DosenUjian::class, 'index']);
        Route::post('ujian',                             [DosenUjian::class, 'store']);
        Route::put('ujian/{id}',                         [DosenUjian::class, 'update']);
        Route::delete('ujian/{id}',                      [DosenUjian::class, 'destroy']);
        Route::get('ujian/{id}/soal',                    [DosenUjian::class, 'indexSoal']);
        Route::post('ujian/{id}/soal',                   [DosenUjian::class, 'storeSoal']);
        Route::put('ujian/{ujianId}/soal/{soalId}',      [DosenUjian::class, 'updateSoal']);
        Route::delete('ujian/{ujianId}/soal/{soalId}',   [DosenUjian::class, 'destroySoal']);
        Route::get('ujian/{id}/hasil',                   [DosenUjian::class, 'hasil']);
    });

    // ── ADMIN ──────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('dashboard', [AdminDashboard::class, 'index']);

        Route::apiResource('mahasiswa',   MahasiswaController::class);
        Route::apiResource('dosen',       DosenController::class);
        Route::apiResource('mata-kuliah', MataKuliahController::class);
        Route::apiResource('kelas',       AdminKelas::class);
        Route::apiResource('semester',    SemesterController::class);

        // Program Studi
        Route::get('program-studi',       [ProgramStudiController::class, 'index']);
        Route::post('program-studi',      [ProgramStudiController::class, 'store']);
        Route::put('program-studi/{id}',  [ProgramStudiController::class, 'update']);

        // Pembayaran
        Route::get('pembayaran',                    [AdminPembayaran::class, 'index']);
        Route::post('pembayaran',                   [AdminPembayaran::class, 'store']);
        Route::put('pembayaran/{id}/konfirmasi',     [AdminPembayaran::class, 'konfirmasi']);
        Route::delete('pembayaran/{id}',            [AdminPembayaran::class, 'destroy']);

        // Surat
        Route::get('surat',               [SuratController::class, 'index']);
        Route::put('surat/{id}/proses',   [SuratController::class, 'proses']);
        Route::put('surat/{id}/selesai',  [SuratController::class, 'selesai']);
        Route::put('surat/{id}/tolak',    [SuratController::class, 'tolak']);

        // Pengumuman
        Route::get('pengumuman',          [AdminPengumuman::class, 'index']);
        Route::post('pengumuman',         [AdminPengumuman::class, 'store']);
        Route::get('pengumuman/{id}',     [AdminPengumuman::class, 'show']);
        Route::put('pengumuman/{id}',     [AdminPengumuman::class, 'update']);
        Route::delete('pengumuman/{id}',  [AdminPengumuman::class, 'destroy']);

        // KTM
        Route::get('ktm/{mahasiswaId}',           [KtmController::class, 'show']);
        Route::post('ktm/{mahasiswaId}/generate', [KtmController::class, 'generate']);

        // Laporan
        Route::get('laporan/mahasiswa', [LaporanController::class, 'mahasiswa']);
        Route::get('laporan/keuangan',  [LaporanController::class, 'keuangan']);
        Route::get('laporan/akademik',  [LaporanController::class, 'akademik']);

        // Ujian (monitoring)
        Route::get('ujian',               [AdminUjian::class, 'index']);
        Route::get('ujian/{id}',          [AdminUjian::class, 'show']);
        Route::put('ujian/{id}/batalkan', [AdminUjian::class, 'batalkan']);
    });
});
