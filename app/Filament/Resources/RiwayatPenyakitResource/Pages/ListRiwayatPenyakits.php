<?php

namespace App\Filament\Resources\RiwayatPenyakitResource\Pages;

use App\Filament\Resources\RiwayatPenyakitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRiwayatPenyakits extends ListRecords
{
    protected static string $resource = RiwayatPenyakitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
