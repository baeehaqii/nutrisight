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
            'riwayat_penyakit_id' => 'nullable|integer|exists:riwayat_penyakits,id',
            'jenis_kelamin' => ['nullable', 'string', Rule::in(['L', 'P'])],
            'tanggal_lahir' => 'nullable|date_format:Y-m-d',
            'no_wa' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update data user
        $user->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new RegisterResource($user->fresh()) // Ambil data terbaru dari DB
        ]);
    }
}