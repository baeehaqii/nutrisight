<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegisterResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Register a new user
     */
    public function store(Request $request)
    {
        try {
            // Validasi input - hanya nama_depan, email, password
            $validator = Validator::make($request->all(), [
                'nama_depan' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buat user baru - hanya field yang dibutuhkan
            $user = User::create([
                'nama_depan' => $request->nama_depan,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Set role pengguna
            $user->assignRole('pengguna');

            // Generate token jika menggunakan Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => new RegisterResource($user),
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile (optional - untuk testing)
     */
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => new RegisterResource($request->user())
        ]);
    }
}