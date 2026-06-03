<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';

    protected $fillable = [
        'user_id',
        'nama',
        'jabatan',
        'no_hp',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'dibuat_oleh');
    }

    public function surat()
    {
        return $this->hasMany(Surat::class, 'diproses_oleh');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'dikonfirmasi_oleh');
    }
}