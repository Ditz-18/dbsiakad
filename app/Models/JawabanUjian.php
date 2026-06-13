<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanUjian extends Model
{
    protected $table = 'jawaban_ujian';

    protected $fillable = [
        'ujian_id', 'mahasiswa_id', 'soal_id', 'jawaban', 'ragu',
    ];

    protected $casts = [
        'ragu' => 'boolean',
    ];

    public function soal()
    {
        return $this->belongsTo(SoalUjian::class, 'soal_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
