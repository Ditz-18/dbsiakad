<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $table = 'surat';

    protected $fillable = [
        'no_pengajuan',
        'mahasiswa_id',
        'jenis_surat',
        'keperluan',
        'status',
        'diproses_oleh',
        'diproses_at',
        'catatan',
    ];

    protected $casts = [
        'diproses_at' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(Admin::class, 'diproses_oleh');
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'Menunggu');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'Selesai');
    }
}