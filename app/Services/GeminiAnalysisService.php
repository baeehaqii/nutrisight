<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GeminiAnalysisService
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('GEMINI_SERVICE_URL', 'http://localhost:3000/api/analyze-product');
    }

    public function analyzeProductImage($imagePath)
    {
        try {
            // Debug: Log path yang diterima
            Log::info('GeminiService - Received path:', ['imagePath' => $imagePath]);

            // Pastikan path dimulai dengan storage/ jika perlu
            $fullPath = $imagePath;
            if (!str_starts_with($imagePath, 'scan-produk-upload/')) {
                $fullPath = 'scan-produk-upload/' . $imagePath;
            }

            Log::info('GeminiService - Full path:', ['fullPath' => $fullPath]);

            // Cek apakah file ada
            if (!Storage::exists($fullPath)) {
                // Coba path alternatif
                $alternativePaths = [
                    $imagePath,
                    'public/' . $imagePath,
                    'public/scan-produk-upload/' . basename($imagePath),
                ];

                $foundPath = null;
                foreach ($alternativePaths as $altPath) {
                    if (Storage::exists($altPath)) {
                        $foundPath = $altPath;
                        break;
                    }
                }

                if (!$foundPath) {
                    // List semua file di directory untuk debugging
                    $files = Storage::files('scan-produk-upload');
                    Log::info('Available files:', ['files' => $files]);

                    throw new \Exception('File gambar tidak ditemukan. Path: ' . $fullPath);
                }

                $fullPath = $foundPath;
            }

            Log::info('GeminiService - Using path:', ['finalPath' => $fullPath]);

            // Ambil file dari storage
            $imageData = Storage::get($fullPath);
            $base64Image = base64_encode($imageData);

            return $this->sendToGeminiService($base64Image, $this->getMimeType($fullPath));

        } catch (\Exception $e) {
            Log::error('Gemini Analysis Error: ' . $e->getMessage());
            throw $e;
        }
    }

    // Method baru untuk menangani file path langsung
    public function analyzeProductImageFromPath($filePath)
    {
        try {
            // Debug: Log path yang diterima
            Log::info('GeminiService - Received file path:', ['filePath' => $filePath]);

            // Cek apakah file ada
            if (!file_exists($filePath)) {
                throw new \Exception('File gambar tidak ditemukan: ' . $filePath);
            }

            // Baca file langsung
            $imageData = file_get_contents($filePath);
            $base64Image = base64_encode($imageData);

            return $this->sendToGeminiService($base64Image, $this->getMimeTypeFromPath($filePath));

        } catch (\Exception $e) {
            Log::error('Gemini Analysis Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function sendToGeminiService($base64Image, $mimeType)
    {
        // Cek apakah Node.js service aktif
        $healthCheck = Http::timeout(5)->get('http://localhost:3000/health');
        if (!$healthCheck->successful()) {
            throw new \Exception('Gemini service tidak aktif. Pastikan Node.js service berjalan di port 3000');
        }

        // Kirim request ke Node.js service
        $response = Http::timeout(60)->post($this->apiUrl, [
            'image' => $base64Image,
            'mimeType' => $mimeType
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            Log::info('GeminiService - Response:', ['response' => $responseData]);

            return $this->parseGeminiResponse($responseData);
        }

        throw new \Exception('Failed to analyze image: ' . $response->body());
    }

    private function getMimeType($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        return match (strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg'
        };
    }

    private function getMimeTypeFromPath($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        return match (strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg'
        };
    }

    private function parseGeminiResponse($response)
    {
        $text = $response['analysis'] ?? '';

        // Extract data menggunakan regex
        $namaProduk = $this->extractValue($text, 'Nama Produk:', '(.*?)(?:\n|$)');
        $jenisProduk = $this->extractValue($text, 'Jenis Produk:', '(.*?)(?:\n|$)');
        $totalGula = $this->extractGulaValue($text);
        $rekomendasi = $this->extractRekomendasi($text);

        return [
            'nama_produk' => $namaProduk ?: 'Tidak Terdeteksi',
            'jenis_produk' => $jenisProduk ?: 'Tidak Terdeteksi',
            'total_gula' => $totalGula ?: 0,
            'rekomendasi' => $rekomendasi ?: 'Rekomendasi tidak tersedia'
        ];
    }

    private function extractValue($text, $label, $pattern)
    {
        $regex = '/' . preg_quote($label, '/') . '\s*' . $pattern . '/i';
        if (preg_match($regex, $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    private function extractGulaValue($text)
    {
        // Log full response untuk debugging
        Log::info('Full AI Response for debugging:', ['response' => $text]);

        // Pattern yang lebih spesifik untuk menangkap nilai gula per takaran saji
        $patterns = [
            // Pattern untuk format dengan "per takaran saji"
            '/Gula \(per takaran saji\):\s*([0-9.,]+)(?:\s*g|gram)/i',
            '/Sugars \(per takaran saji\):\s*([0-9.,]+)(?:\s*g|gram)/i',
            '/Total Sugars \(per takaran saji\):\s*([0-9.,]+)(?:\s*g|gram)/i',

            // Pattern untuk baris spesifik dengan nilai exact
            '/Gula \(per takaran saji\):\s*\[?([0-9.,]+)(?:\s*g|gram)?\]?/i',
            '/\[NILAI EXACT DARI LABEL TANPA DIHITUNG ULANG\].*?([0-9.,]+)(?:\s*g|gram)/i',

            // Pattern umum yang lebih ketat
            '/(?:^|\n)\s*Gula[^:]*:\s*([0-9.,]+)(?:\s*g|gram)/im',
            '/(?:^|\n)\s*Sugars[^:]*:\s*([0-9.,]+)(?:\s*g|gram)/im',

            // Pattern untuk mencari nilai yang bukan per 100ml
            '/Gula.*?takaran.*?([0-9.,]+)(?:\s*g|gram)/i',
            '/([0-9.,]+)(?:\s*g|gram).*?per.*?takaran/i',
        ];

        foreach ($patterns as $index => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $value = floatval(str_replace(',', '.', $matches[1]));

                // Log untuk debugging
                Log::info('Gula pattern matched:', [
                    'pattern_index' => $index,
                    'pattern' => $pattern,
                    'matched_text' => $matches[0],
                    'extracted_value' => $value
                ]);

                // Validasi nilai (untuk minuman kemasan, gula biasanya 5-50 gram per serving)
                if ($value > 0 && $value <= 100) {
                    return $value;
                }
            }
        }

        // Fallback: cari semua mention angka + gram dan pilih yang paling masuk akal
        if (preg_match_all('/([0-9.,]+)(?:\s*g|gram)/i', $text, $allMatches)) {
            Log::info('All gram values found:', ['values' => $allMatches[1]]);

            foreach ($allMatches[1] as $value) {
                $numValue = floatval(str_replace(',', '.', $value));
                // Untuk minuman, nilai gula biasanya 10-30 gram per serving
                if ($numValue >= 10 && $numValue <= 50) {
                    Log::info('Selected potential sugar value:', ['value' => $numValue]);
                    return $numValue;
                }
            }
        }

        return 0;
    }

    private function extractRekomendasi($text)
    {
        // Extract bagian "Saran Konsumsi Umum" dan "Kesimpulan Ringkas"
        $rekomendasi = '';

        if (preg_match('/4\.\s*Saran Konsumsi Umum:(.*?)5\.\s*Kesimpulan Ringkas:/s', $text, $matches)) {
            $rekomendasi .= "Saran Konsumsi:\n" . trim($matches[1]) . "\n\n";
        }

        if (preg_match('/5\.\s*Kesimpulan Ringkas:(.*?)(?:\n\n|\[|$)/s', $text, $matches)) {
            $rekomendasi .= "Kesimpulan:\n" . trim($matches[1]);
        }

        // Jika tidak ada format yang cocok, ambil teks dari bagian saran dan kesimpulan
        if (empty($rekomendasi)) {
            if (preg_match('/Saran.*?:(.*?)(?:Kesimpulan|$)/si', $text, $matches)) {
                $rekomendasi = trim($matches[1]);
            }
        }

        return $rekomendasi ?: 'Rekomendasi tidak tersedia';
    }
}