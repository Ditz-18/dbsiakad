<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanBimbingan extends Model
{
    protected $table = 'catatan_bimbingan';

    protected $fillable = [
        'mahasiswa_id', 'dosen_id',
        'topik', 'catatan', 'tindak_lanjut',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }
}
