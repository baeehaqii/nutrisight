<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ScanProduk extends Model
{
    protected $fillable = [
        'user_id',
        'nama_produk',
        'jenis_produk',
        'takaran_saji',
        'grade_produk',
        'tanggal_scan',
        'gula_per_saji', //total gula dalam produk per takaran saji
        'gula_per_100ml',
        'gambar_produk',
        'rekomendasi_personalisasi',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function gambarProduk(): Attribute
    {
        return Attribute::make(
            // Fungsi 'get' ini akan dipanggil saat Anda mengambil data
            get: fn ($value) => $value ? Storage::url($value) : null,
        );
    }
}
