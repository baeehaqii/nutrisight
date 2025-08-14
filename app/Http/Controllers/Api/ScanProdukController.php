<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScanProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; // <-- TAMBAHKAN IMPORT INI

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

        // ▼▼▼ LOGIKA BARU DIMULAI DI SINI ▼▼▼

        // 1. Dapatkan objek pengguna yang sedang login
        $user = auth()->user();
        $validated['user_id'] = $user->id;

        // 2. Buat entri riwayat scan seperti biasa
        $scanProduk = ScanProduk::create($validated);

        // 3. Logika untuk memperbarui total konsumsi gula harian
        $today = Carbon::today()->toDateString();
        $gulaBaru = $validated['gula_per_saji'] ?? 0;

        // Jika tanggal terakhir konsumsi bukan hari ini, reset totalnya ke 0
        if ($user->tanggal_konsumsi_terakhir != $today) {
            $user->konsumsi_gula_harian = 0;
        }

        // Tambahkan gula dari scan baru ke total harian
        $user->konsumsi_gula_harian += $gulaBaru;
        $user->tanggal_konsumsi_terakhir = $today;
        $user->save(); // Simpan perubahan pada data pengguna

        // ▲▲▲ AKHIR DARI LOGIKA BARU ▲▲▲

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