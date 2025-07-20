<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegisterResource;
use Illuminate\Http\Request;

class GetProfileController extends Controller
{

    public function __invoke(Request $request)
    {
        // Middleware 'auth:sanctum' akan secara otomatis mengidentifikasi user dari token.
        // Kita bisa mengambilnya langsung dari object request.
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully',
            'data' => new RegisterResource($user)
        ]);
    }
}