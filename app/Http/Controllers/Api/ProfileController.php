<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegisterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
}