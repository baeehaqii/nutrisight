<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use function Pest\Laravel\options;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi User')
                    ->schema([
                        Forms\Components\TextInput::make('nama_depan')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_belakang')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_wa')
                            ->maxLength(255),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->placeholder('Pilih Jenis Kelamin')
                            ->options([
                                'laki-laki' => 'Laki-laki',
                                'perempuan' => 'Perempuan',
                            ]),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->maxDate(now()) // Pastikan tanggal lahir tidak di masa depan
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                // Update usia otomatis
                                if ($state) {
                                    // Pastikan kita mendapatkan nilai positif dengan abs() 
                                    // dan format integer dengan floor()
                                    $birthDate = \Carbon\Carbon::parse($state);
                                    $usia = floor(abs($birthDate->diffInYears(now())));
                                    $set('usia', $usia);
                                }
                            }),
                        Forms\Components\TextInput::make('usia')
                            ->numeric()
                            ->reactive()
                            ->suffix(' Tahun')
                            ->disabled()
                            ->dehydrated(true),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('email_verified_at'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->revealable()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('foto_profile')
                            ->image()
                            ->maxSize(1024)
                            ->preserveFilenames()
                            ->directory('profile_pictures')
                            ->visibility('public')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->columnSpanFull(),
                    ])->columns(3),
                Section::make('Preferensi dan Riwayat')
                    ->schema([
                        Forms\Components\CheckboxList::make('riwayat_penyakit')
                            ->label('Riwayat Penyakit')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(5)
                            ->options([
                                'diabetes' => 'Diabetes',
                                'diabetes_melitus' => 'Diabetes Melitus',
                                'jantung' => 'Penyakit Jantung',
                                'stroke' => 'Stroke',
                                'asam_lambung' => 'Asam Lambung',
                                'asma' => 'Asma',
                                'kanker' => 'Kanker',
                                'liver' => 'Penyakit Liver',
                                'gagal_ginjal' => 'Gagal Ginjal',
                                'tuberkulosis' => 'Tuberkulosis',
                                'gangguan_pernapasan' => 'Gangguan Pernapasan',
                                'gangguan_pencernaan' => 'Gangguan Pencernaan',
                                'hipertensi' => 'Hipertensi',
                                'kolesterol' => 'Kolesterol Tinggi',
                                'tidak_ada' => 'Tidak Ada Riwayat Penyakit',
                            ]),
                    ]),
                Section::make('Hasil Model')
                    ->schema([
                        Forms\Components\Textarea::make('hasil_model')
                            ->label('Hasil Model')
                            ->json()
                            ->maxLength(5000)
                            ->helperText('Hasil model prediksi kesehatan pengguna.'),
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_depan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_belakang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_wa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('usia')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('foto_profile')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
