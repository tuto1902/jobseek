<?php

namespace App\Filament\Widgets;

use App\Models\ConversionEvent;
use App\Models\PageVisit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RegistrationConversionStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalVisits = PageVisit::where('created_at', '>=', now()->subMonth())->count();
        $totalRegistrations = User::where('created_at', '>=', now()->subMonth())->count();
        $conversionRate = $totalVisits > 0 ? ($totalRegistrations / $totalVisits) * 100 : 0;

        $registrationsBySource = ConversionEvent::where('event_type', 'registration')
            ->where('created_at', '>=', now()->subMonth())
            ->get()
            ->groupBy(fn ($event) => $event->event_data['source'] ?? 'unknown')
            ->map->count();

        return [
            Stat::make('Conversion Rate', number_format($conversionRate, 2).'%')
                ->description('Visits to registrations (30 days)')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Organic Registrations', $registrationsBySource->get('organic', 0))
                ->description('Direct signups')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make('Referral Registrations', $registrationsBySource->get('referral', 0))
                ->description('From referrals')
                ->descriptionIcon('heroicon-m-link')
                ->color('warning'),
        ];
    }
}
