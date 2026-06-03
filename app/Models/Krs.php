<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Krs extends Model
{
    protected $table = 'krs';

    protected $fillable = [
        'mahasiswa_id',
        'kelas_id',
        'semester_id',
        'status',
        'diajukan_at',
        'diproses_at',
        'catatan_pa',
    ];

    protected $casts = [
        'diajukan_at' => 'datetime',
        'diproses_at' => 'datetime',
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
}