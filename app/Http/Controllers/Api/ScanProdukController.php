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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'nama_produk' => 'required|string|max:255',
            'jenis_produk' => 'nullable|string|max:255',
            'takaran_saji' => 'nullable|string|max:255',
            'grade_produk' => 'nullable|string|max:255',
            'tanggal_scan' => 'nullable|date_format:Y-m-d',
            'gula_per_saji' => 'nullable|integer',
            'gula_per_100ml' => 'nullable|integer',
            'gambar_produk' => 'nullable|string|max:255',
            'rekomendasi_personalisasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $scanProduk = ScanProduk::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Scan produk berhasil disimpan',
            'data' => $scanProduk,
        ], 201);
    }

    public function index(Request $request)
    {
        $results = ScanProduk::with('user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data hasil scan produk berhasil diambil',
            'data' => $results,
        ], 200);
    }

    public function show($id)
    {
        $scanProduk = ScanProduk::with('user')->find($id);

        if (!$scanProduk) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail riwayat scan berhasil diambil',
            'data' => $scanProduk,
        ], 200);
    }
}