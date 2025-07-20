<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\RiwayatPenyakit;
use App\Http\Controllers\Controller;
use App\Http\Resources\RiwayatPenyakitResource;

class RiwayatPenyakitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = RiwayatPenyakit::where('status', 'aktif')->get();
        return response()->json([
            'success' => true,
            'data' => RiwayatPenyakitResource::collection($data)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
