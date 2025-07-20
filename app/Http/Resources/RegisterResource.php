<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_depan' => $this->nama_depan,
            'nama_belakang' => $this->nama_belakang,
            'no_wa' => $this->no_wa,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'usia' => $this->usia,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'foto_profile' => $this->foto_profile,
            'riwayat_penyakit' => $this->riwayat_penyakit,
            'target_konsumsi_gula' => $this->target_konsumsi_gula,
            'target_konsumsi_gula_value' => $this->target_konsumsi_gula_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}