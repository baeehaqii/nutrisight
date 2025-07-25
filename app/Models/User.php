<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'riwayat_penyakit_id',
        'nama_depan',
        'nama_belakang',
        'email',
        'email_verified_at',
        'no_wa',
        'tanggal_lahir',
        'jenis_kelamin',
        'usia',
        'riwayat_penyakit',
        'hasil_model',
        'foto_profile',
        'target_konsumsi_gula',
        'target_konsumsi_gula_value',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'riwayat_penyakit' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $user->email_verified_at = now();
        });

        static::saving(function ($user) {
            if ($user->isDirty('tanggal_lahir') && $user->tanggal_lahir) {
                $user->usia = Carbon::parse($user->tanggal_lahir)->age;
            }
        });
    }

    /**
     * Get the user's name for Filament.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->nama_depan . ' ' . $this->nama_belakang ?: 'User';
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->nama_depan . ' ' . $this->nama_belakang ?: 'User';
    }

    public function riwayatPenyakit()
    {
        return $this->belongsToMany(RiwayatPenyakit::class);
    }
}
