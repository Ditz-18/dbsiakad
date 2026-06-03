<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogUjian extends Model
{
    protected $table = 'log_ujian';

    protected $fillable = [
        'ujian_id',
        'mahasiswa_id',
        'no_pelanggaran',
        'deskripsi',
        'dibatalkan',
        'terjadi_at',
    ];

    protected $casts = [
        'dibatalkan'     => 'boolean',
        'terjadi_at'     => 'datetime',
        'no_pelanggaran' => 'integer',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('dibatalkan', false);
    }
}