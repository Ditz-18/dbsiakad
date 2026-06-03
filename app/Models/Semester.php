<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semester';

    protected $fillable = [
        'nama',
        'tahun_akademik',
        'tipe',
        'tanggal_mulai',
        'tanggal_selesai',
        'krs_buka',
        'krs_tutup',
        'nilai_buka',
        'nilai_tutup',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai'  => 'date',
        'tanggal_selesai'=> 'date',
        'krs_buka'       => 'date',
        'krs_tutup'      => 'date',
        'nilai_buka'     => 'date',
        'nilai_tutup'    => 'date',
    ];

    public function krs()
    {
        return $this->hasMany(Krs::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function ujian()
    {
        return $this->hasMany(Ujian::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'Aktif');
    }
}