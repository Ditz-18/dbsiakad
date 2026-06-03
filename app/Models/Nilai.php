<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $table = 'nilai';

    protected $fillable = [
        'mahasiswa_id',
        'kelas_id',
        'semester_id',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'grade',
        'bobot',
        'status',
    ];

    protected $casts = [
        'nilai_tugas'  => 'float',
        'nilai_uts'    => 'float',
        'nilai_uas'    => 'float',
        'nilai_akhir'  => 'float',
        'bobot'        => 'float',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function scopeLulus($query)
    {
        return $query->where('status', 'Lulus');
    }
}