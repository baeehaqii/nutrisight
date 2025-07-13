<?php

namespace App\Filament\Resources\RiwayatPenyakitResource\Pages;

use App\Filament\Resources\RiwayatPenyakitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiwayatPenyakit extends EditRecord
{
    protected static string $resource = RiwayatPenyakitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
