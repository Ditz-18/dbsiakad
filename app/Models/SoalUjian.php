<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoalUjian extends Model
{
    protected $table = 'soal_ujian';

    protected $fillable = [
        'ujian_id', 'nomor', 'pertanyaan',
        'tipe', 'pilihan', 'jawaban_benar', 'bobot',
    ];

    protected $casts = [
        'pilihan' => 'array',
        'bobot'   => 'integer',
        'nomor'   => 'integer',
    ];

    // Sembunyikan jawaban_benar saat dikirim ke mahasiswa
    protected $hidden = [];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function jawaban()
    {
        return $this->hasMany(JawabanUjian::class, 'soal_id');
    }
}
