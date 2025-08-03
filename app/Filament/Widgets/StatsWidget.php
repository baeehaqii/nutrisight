<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\RiwayatPenyakit;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsWidget extends BaseWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pengguna Aplikasi', User::count() . ' pengguna')
                ->description('Total Penngguna Aplikasi')
                ->descriptionIcon('heroicon-m-information-circle'),
            Stat::make('Pengguna Aktif', 11 . ' pengguna')
                ->color('success')
                ->descriptionIcon('heroicon-m-information-circle'),
            Stat::make('Pengguna Tidak Aktif', 5 . ' pengguna')
                ->color('danger')
                ->descriptionIcon('heroicon-m-information-circle'),
            Stat::make('Total Riwayat Penyakit Aktif', RiwayatPenyakit::where('status', 'aktif')->count() . ' riwayat')
                ->color('warning')
                ->descriptionIcon('heroicon-m-information-circle'),
        ];
    }
}
