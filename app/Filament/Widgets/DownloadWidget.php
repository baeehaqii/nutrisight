<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class DownloadWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Download Aplikasi di Play Store & App Store';

    protected static bool $isLazy = false;
    public ?string $filter = 'Minggu Ini';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Play Store',
                    'data' => [7, 22, 30, 0, 0, 0, 0],
                    'borderColor' => '#2732A5',
                    'backgroundColor' => '#F5F5FD',
                    
                ],
                [
                    'label' => 'App Store',
                    'data' => [1, 12, 7, 0, 0, 0, 0],
                    'borderColor' => '#B20F16',
                    'backgroundColor' => '#FCF2F3',
                ],
            ],
            'labels' => ['Juni', 'Juli', 'Agustus', 'September', 'Oktober','November', 'Desember'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'Hari Ini' => 'Hari Ini',
            'Minggu Ini' => 'Minggu Ini',
            'Bulan Ini' => 'Bulan Ini',
            'Tahun Ini' => 'Tahun Ini',
        ];
    }
}
