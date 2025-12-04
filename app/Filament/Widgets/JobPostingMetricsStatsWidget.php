<?php

namespace App\Filament\Widgets;

use App\Models\JobClick;
use App\Models\JobPosting;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobPostingMetricsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalJobs = JobPosting::count();
        $activeJobs = JobPosting::where('status', 'active')->count();

        $totalClicks = JobClick::where('created_at', '>=', now()->subMonth())->count();
        $clicksThisWeek = JobClick::where('created_at', '>=', now()->subWeek())->count();

        $avgCTR = $totalJobs > 0 ? ($totalClicks / $totalJobs) : 0;

        return [
            Stat::make('Total Job Postings', $totalJobs)
                ->description($activeJobs.' active')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success'),

            Stat::make('Total Clicks (30 days)', $totalClicks)
                ->description($clicksThisWeek.' this week')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('primary'),

            Stat::make('Avg Click-Through Rate', number_format($avgCTR, 2))
                ->description('Clicks per job')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color('warning'),
        ];
    }
}
