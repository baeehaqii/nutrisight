<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\ScanProduk;
use Illuminate\Http\Request;
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
        return response()->json([
            'success' => true,
            'data' => new RegisterResource($request->user())
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'riwayat_penyakit' => 'nullable|array',
            'riwayat_penyakit.*' => 'string|exists:riwayat_penyakits,nama_penyakit',
            'jenis_kelamin' => ['nullable', 'string', Rule::in(['L', 'P', 'laki-laki', 'perempuan'])],
            'tanggal_lahir' => 'nullable|date_format:Y-m-d',
            'no_wa' => 'nullable|string|max:20',
            'target_konsumsi_gula' => ['nullable', 'string', Rule::in(['harian', 'mingguan', 'bulanan'])],
            'target_konsumsi_gula_value' => 'nullable|numeric|required_with:target_konsumsi_gula',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validatedData = $validator->validated();

            $user->update($validatedData);

            if (isset($validatedData['riwayat_penyakit'])) {
                $riwayatPenyakitIds = \App\Models\RiwayatPenyakit::whereIn('nama_penyakit', $validatedData['riwayat_penyakit'])->pluck('id')->toArray();
                $user->riwayatPenyakits()->sync($riwayatPenyakitIds);
                \Log::info('Riwayat Penyakit IDs:', $riwayatPenyakitIds);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => new RegisterResource($user->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getOcrContext(Request $request)
    {
        $user = $request->user();
        
        // 1. Hitung konsumsi gula hari ini langsung dari database
        $dailySugarConsumption = ScanProduk::where('user_id', $user->id)
            ->whereDate('tanggal_scan', Carbon::today())
            ->sum('gula_per_saji');

        // 2. Kembalikan data profil dan data terhitung dalam satu paket
        return response()->json([
            'success' => true,
            'data' => [
                'profile' => new RegisterResource($user),
                'daily_sugar_consumption' => $dailySugarConsumption
            ]
        ]);
    }
}