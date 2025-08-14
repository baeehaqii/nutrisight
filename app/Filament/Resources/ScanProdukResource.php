<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\ScanProduk;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ScanProdukResource\Pages;
use App\Filament\Resources\ScanProdukResource\RelationManagers;
use Filament\Forms\Components\Actions\Action;
use App\Services\GeminiAnalysisService;

class ScanProdukResource extends Resource
{
    protected static ?string $model = ScanProduk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('nama_produk')
                                ->label('Nama Produk')
                                ->required(),
                            Forms\Components\TextInput::make('jenis_produk')
                                ->label('Jenis Produk')
                                ->required(),
                            Forms\Components\TextInput::make('takaran_saji')
                                ->label('Takaran Saji')
                                ->required(),
                            Forms\Components\TextInput::make('grade_produk')
                                ->label('Grade Produk')
                                ->required(),
                            Forms\Components\DatePicker::make('tanggal_scan')
                                ->label('Tanggal Scan')
                                ->required(),
                            Forms\Components\TextInput::make('gula_per_saji')
                                ->label('Gula per Saji')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('gula_per_100ml')
                                ->label('Gula per 100ml')
                                ->numeric()
                                ->required(),
                            Forms\Components\FileUpload::make('gambar_produk')
                                ->label('Gambar Produk')
                                ->directory('scan-produk-upload')
                                ->required(),
                            Forms\Components\Textarea::make('rekomendasi_personalisasi')
                                ->label('Rekomendasi Personalisasi')
                                ->rows(5)
                                ->required(),
                        ])->columnSpan([
                                'lg' => 2,
                            ]),

                    Section::make()
                        ->schema([
                            Forms\Components\FileUpload::make('gambar_produk')
                                ->directory('scan-produk-upload')
                                ->columnSpanFull()
                                ->afterStateUpdated(function (callable $set, $state) {
                                    // Reset fields when new image is uploaded
                                    $set('nama_produk', '');
                                    $set('jenis_produk', '');
                                    $set('total_gula', '');
                                    $set('rekomendasi', '');
                                }),

                            Forms\Components\Actions::make([
                                Action::make('analyze_with_ai')
                                    ->label('Analisis dengan AI')
                                    ->icon('heroicon-o-sparkles')
                                    ->color('primary')
                                    ->action(function (callable $get, callable $set) {
                                        // Gunakan $get untuk mendapatkan nilai gambar_produk
                                        $gambarProduk = $get('gambar_produk');

                                        // Debug: Log nilai gambar_produk
                                        Log::info('Debug - Gambar Produk Value:', ['gambar_produk' => $gambarProduk]);

                                        if (empty($gambarProduk)) {
                                            Notification::make()
                                                ->title('Error')
                                                ->body('Silakan upload gambar produk terlebih dahulu')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        try {
                                            $geminiService = app(GeminiAnalysisService::class);

                                            // Tangani TemporaryUploadedFile dari Filament
                                            $filePath = null;

                                            if (is_array($gambarProduk)) {
                                                // Ambil file pertama dari array
                                                $firstFile = reset($gambarProduk);

                                                if ($firstFile instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                    // Untuk TemporaryUploadedFile, gunakan path() method
                                                    $filePath = $firstFile->path();
                                                    Log::info('Debug - TemporaryUploadedFile Path:', ['path' => $filePath]);
                                                } elseif (is_array($firstFile)) {
                                                    // Jika masih array, cari TemporaryUploadedFile di dalamnya
                                                    foreach ($firstFile as $item) {
                                                        if ($item instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                            $filePath = $item->path();
                                                            break;
                                                        }
                                                    }
                                                } else {
                                                    // Jika string biasa (file yang sudah disimpan)
                                                    $filePath = storage_path('app/scan-produk-upload/' . $firstFile);
                                                }
                                            } else {
                                                // Jika bukan array, kemungkinan string nama file
                                                $filePath = storage_path('app/scan-produk-upload/' . $gambarProduk);
                                            }

                                            // Debug: Log file path
                                            Log::info('Debug - Final File Path:', ['filePath' => $filePath]);

                                            if (empty($filePath) || !file_exists($filePath)) {
                                                throw new \Exception('File gambar tidak ditemukan atau tidak valid: ' . $filePath);
                                            }

                                            Notification::make()
                                                ->title('Sedang Menganalisis...')
                                                ->body('AI sedang menganalisis gambar produk Anda')
                                                ->info()
                                                ->send();

                                            $result = $geminiService->analyzeProductImageFromPath($filePath);

                                            $set('nama_produk', $result['nama_produk']);
                                            $set('jenis_produk', $result['jenis_produk']);
                                            $set('total_gula', $result['total_gula']);
                                            $set('rekomendasi', $result['rekomendasi']);

                                            Notification::make()
                                                ->title('Analisis Selesai')
                                                ->body('Produk berhasil dianalisis oleh AI')
                                                ->success()
                                                ->send();

                                        } catch (\Exception $e) {
                                            Log::error('Gemini Analysis Error: ' . $e->getMessage(), [
                                                'trace' => $e->getTraceAsString(),
                                                'gambar_produk' => $gambarProduk
                                            ]);

                                            Notification::make()
                                                ->title('Error')
                                                ->body('Gagal menganalisis produk: ' . $e->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    })
                                    ->visible(fn(callable $get) => !empty($get('gambar_produk')))
                            ])
                        ])
                        ->columnSpan([
                            'lg' => 1,
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.nama_depan')
                    ->label('Pengguna')
                    ->formatStateUsing(fn($record) => $record->user->nama_depan . ' ' . $record->user->nama_belakang)
                    ->searchable(['nama_depan', 'nama_belakang']),
                Tables\Columns\TextColumn::make('nama_produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('takaran_saji')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grade_produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_scan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gula_per_saji')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gula_per_100ml')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gambar_produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rekomendasi_personalisasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScanProduks::route('/'),
            'create' => Pages\CreateScanProduk::route('/create'),
            'edit' => Pages\EditScanProduk::route('/{record}/edit'),
        ];
    }
}
