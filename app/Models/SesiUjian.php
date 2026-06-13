<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiUjian extends Model
{
    protected $table = 'sesi_ujian';

    protected $fillable = [
        'ujian_id', 'mahasiswa_id',
        'mulai_at', 'selesai_at',
        'nilai', 'status', 'pelanggaran',
    ];

    protected $casts = [
        'mulai_at'   => 'datetime',
        'selesai_at' => 'datetime',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
