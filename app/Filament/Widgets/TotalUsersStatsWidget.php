<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\UserSession;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalUsersStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $usersLastMonth = User::where('created_at', '>=', now()->subMonth())->count();
        $usersThisMonth = User::where('created_at', '>=', now()->startOfMonth())->count();

        $activeToday = UserSession::whereDate('started_at', today())->distinct('user_id')->count('user_id');
        $activeThisWeek = UserSession::where('started_at', '>=', now()->subWeek())->distinct('user_id')->count('user_id');
        $activeThisMonth = UserSession::where('started_at', '>=', now()->startOfMonth())->distinct('user_id')->count('user_id');

        $avgSessionDuration = UserSession::whereNotNull('duration')
            ->where('started_at', '>=', now()->subMonth())
            ->avg('duration');

        return [
            Stat::make('Total Users', $totalUsers)
                ->description($usersThisMonth.' new this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart(array_values($this->getUserGrowthChart())),

            Stat::make('Active Today', $activeToday)
                ->description($activeThisWeek.' active this week')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Avg Session Duration', gmdate('i:s', (int) $avgSessionDuration))
                ->description('Minutes:Seconds')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }

    protected function getUserGrowthChart(): array
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }
}
