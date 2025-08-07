<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScanProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScanProdukController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'jenis_produk' => 'nullable|string',
            'takaran_saji' => 'nullable|string',
            'grade_produk' => 'nullable|string',
            'tanggal_scan' => 'nullable|date',
            'gula_per_saji' => 'nullable|integer',
            'gula_per_100ml' => 'nullable|integer',
            'gambar_produk' => 'nullable|string',
            'rekomendasi_personalisasi' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $scanProduk = ScanProduk::create($validated);

        return response()->json([
            'success' => true,
            'data' => $scanProduk,
        ], 201);
    }

    public function index(Request $request)
    {
        $results = ScanProduk::where('user_id', auth()->id())->with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    public function show($id)
    {
        $scanProduk = ScanProduk::where('user_id', auth()->id())
            ->where('id', $id)
            ->with('user')
            ->first();

        if (!$scanProduk) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $scanProduk,
        ]);
    }
}