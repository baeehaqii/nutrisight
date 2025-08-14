<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\ScanProduk;
use Illuminate\Http\Request;
use App\Models\RiwayatPenyakit;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\RegisterResource;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        if ($user->tanggal_konsumsi_terakhir != $today) {
            $user->konsumsi_gula_harian = 0;
            $user->save();
        }

        $user->load('riwayatPenyakits');

        return response()->json([
            'success' => true,
            'data' => new RegisterResource($user)
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'riwayat_penyakit'   => 'nullable|array',
            'riwayat_penyakit.*' => 'string|exists:riwayat_penyakits,nama_penyakit',
            'jenis_kelamin'      => ['nullable', 'string', Rule::in(['laki-laki', 'perempuan'])],
            'tanggal_lahir'      => 'nullable|date_format:Y-m-d',
            'no_wa'              => 'nullable|string|max:20',
            'target_konsumsi_gula' => ['nullable', 'string', Rule::in(['harian', 'mingguan', 'bulanan'])],
            'target_konsumsi_gula_value' => 'nullable|numeric|required_with:target_konsumsi_gula',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $validatedData = $validator->validated();
            $riwayatPenyakitNamas = $validatedData['riwayat_penyakit'] ?? null;
            unset($validatedData['riwayat_penyakit']);

            $user->update($validatedData);

            if ($riwayatPenyakitNamas !== null) {
                $riwayatPenyakitIds = RiwayatPenyakit::whereIn('nama_penyakit', $riwayatPenyakitNamas)->pluck('id')->toArray();
                $user->riwayatPenyakits()->sync($riwayatPenyakitIds);
            }

            // ▼▼▼ PERBAIKAN UTAMA ADA DI SINI ▼▼▼
            // Muat ulang (reload) user beserta relasi riwayatPenyakits yang baru saja di-sync.
            // Ini akan menggantikan panggilan $user->fresh() yang tidak memuat relasi.
            $user->load('riwayatPenyakits');
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                // Kirim kembali data user yang sudah di-refresh dengan relasi terbarunya
                'data'    => new RegisterResource($user)
            ]);
            // ▲▲▲ AKHIR DARI PERBAIKAN ▲▲▲

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the profile',
            ], 500);
        }
    }

    public function getOcrContext(Request $request)
    {
        $user = $request->user();
        $dailySugarConsumption = ScanProduk::where('user_id', $user->id)
            ->whereDate('tanggal_scan', Carbon::today())
            ->sum('gula_per_saji');
            
        $user->load('riwayatPenyakits');
        
        return response()->json([
            'success' => true,
            'data' => [
                'profile' => new RegisterResource($user),
                'daily_sugar_consumption' => $dailySugarConsumption
            ]
        ]);
    }
}