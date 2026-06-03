<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'mahasiswa_id',
        'semester_id',
        'nominal',
        'status',
        'tanggal_bayar',
        'no_referensi',
        'dikonfirmasi_oleh',
    ];

    protected $casts = [
        'nominal'      => 'integer',
        'tanggal_bayar'=> 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function dikonfirmasiOleh()
    {
        return $this->belongsTo(Admin::class, 'dikonfirmasi_oleh');
    }

    public function scopeLunas($query)
    {
        return $query->where('status', 'Lunas');
    }

    public function scopeMenunggak($query)
    {
        return $query->where('status', 'Menunggak');
    }
}