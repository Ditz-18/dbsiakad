<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    protected $table = 'ujian';

    protected $fillable = [
        'nama',
        'kelas_id',
        'dosen_id',
        'semester_id',
        'tipe',
        'durasi',
        'mulai_at',
        'selesai_at',
        'status',
        'max_pelanggaran',
    ];

    protected $casts = [
        'mulai_at'   => 'datetime',
        'selesai_at' => 'datetime',
        'durasi'     => 'integer',
        'max_pelanggaran' => 'integer',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function logUjian()
    {
        return $this->hasMany(LogUjian::class);
    }

    public function scopeBerlangsung($query)
    {
        return $query->where('status', 'Berlangsung');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }
}