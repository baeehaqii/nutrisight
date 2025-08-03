<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class TopUserActiveWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penguna Paling Aktif';

    protected function getData(): array
    {
        return [
            //tambahkan topuser paling aktif membuka aplikasi widget menggunakan data static dulu
            'datasets' => [
                [
                    'label' => 'Top User Active',
                    'data' => [5, 10, 15, 20, 25],
                    'backgroundColor' => '#2196F3',
                ],
            ],
            'labels' => ['User 1', 'User 2', 'User 3', 'User 4', 'User 5'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
}
