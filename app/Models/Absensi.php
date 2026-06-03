<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'mahasiswa_id',
        'kelas_id',
        'semester_id',
        'total_pertemuan',
        'hadir',
        'izin',
        'sakit',
        'alpha',
        'persentase',
    ];

    protected $casts = [
        'persentase' => 'float',
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

    public function scopeMemenuhi($query, int $minPersen = 75)
    {
        return $query->where('persentase', '>=', $minPersen);
    }
}