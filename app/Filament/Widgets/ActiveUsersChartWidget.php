<?php

namespace App\Filament\Widgets;

use App\Models\UserSession;
use Filament\Widgets\ChartWidget;

class ActiveUsersChartWidget extends ChartWidget
{
    protected ?string $heading = 'Daily Active Users (Last 30 Days)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = UserSession::selectRaw('DATE(started_at) as date, COUNT(DISTINCT user_id) as count')
            ->where('started_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = $data->pluck('date')->map(fn ($date) => date('M d', strtotime($date)))->toArray();
        $values = $data->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Active Users',
                    'data' => $values,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
