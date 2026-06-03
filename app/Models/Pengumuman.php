<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'isi',
        'kategori',
        'target',
        'penting',
        'baru',
        'status',
        'dibuat_oleh',
    ];

    protected $casts = [
        'penting' => 'boolean',
        'baru'    => 'boolean',
    ];

    public function dibuatOleh()
    {
        return $this->belongsTo(Admin::class, 'dibuat_oleh');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'Aktif');
    }

    public function scopePenting($query)
    {
        return $query->where('penting', true);
    }

    public function scopeUntuk($query, string $target)
    {
        return $query->where(function ($q) use ($target) {
            $q->where('target', 'Semua')->orWhere('target', $target);
        });
    }
}