const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
require('dotenv').config();

const app = express();
const port = 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json({ limit: '50mb' }));
app.use(bodyParser.urlencoded({ limit: '50mb', extended: true }));

// Import dan Initialize Google Generative AI
let genAI;
try {
    const { GoogleGenerativeAI } = require('@google/generative-ai');

    if (!process.env.GEMINI_API_KEY) {
        throw new Error('GEMINI_API_KEY environment variable is required');
    }

    genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
    console.log('✅ Successfully initialized Google Generative AI');
} catch (error) {
    console.error('❌ Failed to initialize Google Generative AI:', error.message);
    process.exit(1);
}

const systemInstruction = `Kamu adalah asisten analisis nutrisi berpengalaman. Tugas kamu adalah menganalisis gambar label informasi nilai gizi dari produk makanan atau minuman kemasan yang diunggah.

PENTING: Baca dengan SANGAT TELITI angka yang tertulis di label nutrisi. Jangan membagi atau menghitung ulang angka yang sudah tertulis.

Contoh: Jika tertulis "Gula/Sugars 15g" maka nilai gulanya adalah 15g, BUKAN 7.5g atau angka lain.

Ekstraksi Informasi Nutrisi: Ekstrak secara akurat informasi berikut dari teks pada gambar:
- Nama Produk (jika terlihat jelas pada area informasi nilai gizi atau sekitarnya).
- Jenis Produk (Minuman atau makanan)
- Ukuran Takaran Saji (Serving Size) dalam satuan gram (g) atau mililiter (ml).
- Jumlah Gula Total (Total Sugars) per Takaran Saji dalam satuan gram (g). BACA LANGSUNG DARI LABEL - jangan dihitung ulang.
- Jumlah Lemak Jenuh (Saturated Fat) per Takaran Saji dalam satuan gram (g) (hanya jika informasi ini tersedia dan terlihat jelas pada label).

ATURAN EKSTRAKSI GULA:
1. Cari baris yang berisi "Gula" atau "Sugars" atau "Total Sugars"
2. Ambil angka yang tertulis PERSIS di sebelah kata tersebut
3. JANGAN menghitung per 100ml/100g kecuali diminta khusus
4. Jika tertulis "Gula 15g" maka jawab 15
5. Jika tertulis "Sugars 15g" maka jawab 15
6. Jika tertulis "Gula/Sugars 15g" maka jawab 15

Normalisasi Data (Per 100g atau Per 100ml): 
HANYA SETELAH mengekstrak nilai asli dari label, lakukan perhitungan:
- Hitung kandungan Gula Total per 100g (untuk makanan) atau per 100ml (untuk minuman).
- Hitung kandungan Lemak Jenuh per 100g (untuk makanan) atau per 100ml (untuk minuman).

Rumus konversi: (Nilai per takaran saji ÷ Ukuran takaran saji) × 100

Penilaian Produk (Grading System): Berikan peringkat (grade) produk dengan skala A, B, C, atau D berdasarkan nilai per 100ml/100g yang sudah dihitung.

Parameter Gula (per 100ml untuk minuman):
- Grade A: ≤1 gram per 100ml DAN tidak mengandung pemanis buatan
- Grade B: >1 hingga 5 gram per 100ml
- Grade C: >5 hingga 10 gram per 100ml
- Grade D: >10 gram per 100ml

Parameter Lemak Jenuh (per 100ml untuk minuman):
- Grade A: ≤0.7 gram per 100ml
- Grade B: >0.7 hingga 1.2 gram per 100ml
- Grade C: >1.2 hingga 2.8 gram per 100ml
- Grade D: >2.8 gram per 100ml

Penyajian Hasil Analisis: Sajikan hasil analisis dalam format berikut:
[HASIL ANALISIS PRODUK]
1. Informasi Produk yang Diekstrak:
Nama Produk: [Hasil Ekstraksi atau 'Tidak Terdeteksi']
Jenis Produk: [Hasil Ekstraksi atau 'Tidak Terdeteksi']
Takaran Saji: [Hasil Ekstraksi g/ml atau 'Tidak Terdeteksi']
Gula (per takaran saji): [NILAI EXACT DARI LABEL TANPA DIHITUNG ULANG]
Lemak Jenuh (per takaran saji): [Hasil Ekstraksi dalam gram atau 'Tidak Terdeteksi']
Gula (per 100ml): [Hasil Perhitungan dari nilai takaran saji]
Lemak Jenuh (per 100ml): [Hasil Perhitungan dari nilai takaran saji atau 'Tidak Ditemukan']

2. Penilaian Produk:
Grade Produk: [A/B/C/D berdasarkan nilai per 100ml]
Dasar Penilaian Grade: [Penjelasan berdasarkan parameter yang telah ditentukan]

3. Penjelasan Kesehatan Umum:
[Uraikan secara ringkas mengenai dampak kesehatan konsumsi gula berlebih]

4. Saran Konsumsi Umum:
[Berikan saran konsumsi berdasarkan grade yang diberikan]
[Sebutkan batas aman konsumsi gula harian WHO: 25 gram/hari dan Kemenkes RI: 50 gram/hari]

5. Kesimpulan Ringkas:
[Kesimpulan singkat mengenai produk berdasarkan analisis nutrisinya]

CONTOH UNTUK TEHBOTOL:
Jika di label tertulis:
- Takaran Saji: 200ml
- Gula/Sugars: 15g

Maka jawaban yang benar:
- Gula (per takaran saji): 15g
- Gula (per 100ml): 7.5g (15g ÷ 200ml × 100ml)

JANGAN SAMPAI TERTUKAR!`;

// API endpoint for product analysis
app.post('/api/analyze-product', async (req, res) => {
    try {
        const { image, mimeType } = req.body;

        if (!image) {
            return res.status(400).json({
                success: false,
                message: 'Image data is required',
            });
        }

        console.log('Starting image analysis...');

        // Get the generative model
        const model = genAI.getGenerativeModel({
            model: 'gemini-1.5-pro',
            systemInstruction: systemInstruction,
        });

        const imagePart = {
            inlineData: {
                data: image,
                mimeType: mimeType || 'image/jpeg',
            },
        };

        const prompt = 'Analisis gambar label nutrisi produk ini sesuai dengan system instruction yang telah diberikan.';

        const result = await model.generateContent([prompt, imagePart]);
        const response = await result.response;
        const analysisText = response.text();

        console.log('Analysis completed successfully');

        res.json({
            success: true,
            analysis: analysisText,
        });
    } catch (error) {
        console.error('AI Analysis Error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to analyze product',
            error: error.message,
        });
    }
});

app.get('/health', (req, res) => {
    res.json({
        success: true,
        message: 'Google Generative AI Service is running',
        timestamp: new Date().toISOString(),
        genai_initialized: !!genAI,
        api_key_configured: !!process.env.GEMINI_API_KEY,
        package_version: require('@google/generative-ai/package.json').version,
    });
});

app.listen(port, () => {
    console.log(`Google Generative AI Service running at http://localhost:${port}`);
    console.log(`Health check: http://localhost:${port}/health`);
    console.log(`API Key configured: ${!!process.env.GEMINI_API_KEY}`);
});
