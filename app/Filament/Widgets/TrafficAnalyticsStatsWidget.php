<?php

namespace App\Filament\Widgets;

use App\Models\PageVisit;
use App\Models\SearchQuery;
use App\Models\UserSession;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrafficAnalyticsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalVisits = PageVisit::where('created_at', '>=', now()->subMonth())->count();
        $visitsThisWeek = PageVisit::where('created_at', '>=', now()->subWeek())->count();

        $totalSearches = SearchQuery::where('created_at', '>=', now()->subMonth())->count();
        $searchesThisWeek = SearchQuery::where('created_at', '>=', now()->subWeek())->count();

        $totalSessions = UserSession::where('started_at', '>=', now()->subMonth())->count();
        $bouncedSessions = UserSession::where('started_at', '>=', now()->subMonth())
            ->where('duration', '<', 30)
            ->count();
        $bounceRate = $totalSessions > 0 ? ($bouncedSessions / $totalSessions) * 100 : 0;

        return [
            Stat::make('Page Visits (30 days)', $totalVisits)
                ->description($visitsThisWeek.' this week')
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),

            Stat::make('Search Queries (30 days)', $totalSearches)
                ->description($searchesThisWeek.' this week')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('info'),

            Stat::make('Bounce Rate', number_format($bounceRate, 2).'%')
                ->description('Sessions < 30 seconds')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('warning'),
        ];
    }
}
