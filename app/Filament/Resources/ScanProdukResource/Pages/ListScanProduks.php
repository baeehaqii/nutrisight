<?php

namespace App\Filament\Resources\ScanProdukResource\Pages;

use App\Filament\Resources\ScanProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScanProduks extends ListRecords
{
    protected static string $resource = ScanProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
