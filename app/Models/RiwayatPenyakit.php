<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPenyakit extends Model
{
    protected $fillable = [
        'nama_penyakit',
        'status',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
