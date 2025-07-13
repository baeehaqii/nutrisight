<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanProduk extends Model
{
    protected $fillable = [
        'nama_produk',
        'jenis_produk',
        'total_gula',
        'gambar_produk',
        'rekomendasi'
    ];
}
