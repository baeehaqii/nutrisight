<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatPenyakitResource\Pages;
use App\Filament\Resources\RiwayatPenyakitResource\RelationManagers;
use App\Models\RiwayatPenyakit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RiwayatPenyakitResource extends Resource
{
    protected static ?string $model = RiwayatPenyakit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_penyakit')
                    ->required()
                    ->label('Nama Penyakitt')
                    ->placeholder('Masukkan nama penyakit')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                ->label('Status')
                ->default('aktif')
                ->options([
                    'aktif' => 'Aktif',
                    'tidak aktif' => 'Tidak Aktif',
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_penyakit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListRiwayatPenyakits::route('/'),
            'create' => Pages\CreateRiwayatPenyakit::route('/create'),
            'edit' => Pages\EditRiwayatPenyakit::route('/{record}/edit'),
        ];
    }
}
