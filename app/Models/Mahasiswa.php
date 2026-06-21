<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id', 'nim', 'nama', 'program_studi_id',
        'angkatan', 'semester', 'status', 'dosen_pa_id',
        'foto', 'email', 'no_hp', 'alamat',
        'tempat_lahir', 'tanggal_lahir',
        'nama_ayah', 'nama_ibu', 'no_hp_wali',
        'ktm_aktif', 'ktm_berlaku_hingga', 'ktm_generated_at',
    ];

    protected $casts = [
        'ktm_aktif'          => 'boolean',
        'ktm_berlaku_hingga' => 'date',
        'ktm_generated_at'   => 'datetime',
    ];

    public function user()         { return $this->belongsTo(User::class); }
    public function programStudi() { return $this->belongsTo(ProgramStudi::class); }
    public function dosenPa()      { return $this->belongsTo(Dosen::class, 'dosen_pa_id'); }
    public function krs()          { return $this->hasMany(Krs::class); }
    public function nilai()        { return $this->hasMany(Nilai::class); }
    public function absensi()      { return $this->hasMany(Absensi::class); }
    public function pembayaran()   { return $this->hasMany(Pembayaran::class); }
    public function surat()        { return $this->hasMany(Surat::class); }
    public function sesiUjian()    { return $this->hasMany(SesiUjian::class); }
    public function catatanBimbingan() { return $this->hasMany(CatatanBimbingan::class); }
}
