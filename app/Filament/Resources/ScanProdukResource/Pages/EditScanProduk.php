<?php

namespace App\Filament\Resources\ScanProdukResource\Pages;

use App\Filament\Resources\ScanProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScanProduk extends EditRecord
{
    protected static string $resource = ScanProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
