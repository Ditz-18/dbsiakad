<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'dosen';

    protected $fillable = [
        'user_id',
        'nip',
        'nama',
        'program_studi_id',
        'fakultas',
        'jabatan',
        'golongan',
        'email_akademik',
        'no_hp',
        'foto',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    public function mahasiswaBimbingan()
    {
        return $this->hasMany(Mahasiswa::class, 'dosen_pa_id');
    }

    public function ujian()
    {
        return $this->hasMany(Ujian::class);
    }
}