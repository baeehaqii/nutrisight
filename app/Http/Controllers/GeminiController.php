<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeminiController extends Controller
{
    private $geminiService;

    public function __construct(GeminiAnalysisService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function analyzeProduct(Request $request)
    {
        try {
            $imagePath = $request->input('image_path');
            
            if (!$imagePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image path is required'
                ], 400);
            }

            $result = $this->geminiService->analyzeProductImage($imagePath);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Gemini Controller Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }
}