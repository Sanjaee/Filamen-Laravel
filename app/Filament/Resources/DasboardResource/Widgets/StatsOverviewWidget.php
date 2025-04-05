<?php

namespace App\Filament\Widgets;

use App\Models\Owner;
use App\Models\Patient;
use App\Models\Treatment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Hitung total treatment hari ini
        $todayTreatments = Treatment::whereDate('created_at', Carbon::today())->count();
        
        // Hitung pendapatan bulan ini
        $monthlyRevenue = Treatment::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('price');
            
        // Hitung persentase kenaikan pasien bulan ini dibanding bulan lalu
        $patientsThisMonth = Patient::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $patientsLastMonth = Patient::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();
        $patientIncrease = $patientsLastMonth > 0 
            ? round((($patientsThisMonth - $patientsLastMonth) / $patientsLastMonth) * 100, 2) 
            : 100;

        return [
            Stat::make('Total Pemilik', Owner::count())
                ->description('Pemilik terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Total Pasien', Patient::count())
                ->description($patientIncrease >= 0 ? "+{$patientIncrease}% dari bulan lalu" : "{$patientIncrease}% dari bulan lalu")
                ->descriptionIcon($patientIncrease >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($patientIncrease >= 0 ? 'success' : 'danger')
                ->chart([3, 5, 7, 12, 15, 18, 20]),

            Stat::make('Treatment Hari Ini', $todayTreatments)
                ->description('Treatment dilakukan hari ini')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning'),
                
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'))
                ->description('Total pendapatan bulan ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}