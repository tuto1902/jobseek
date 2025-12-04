<?php

namespace App\Filament\Widgets;

use App\Models\SearchQuery;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SearchTrendsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Top Search Queries (Last 30 Days)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = SearchQuery::select('query', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('query')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Search Count',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                ],
            ],
            'labels' => $data->pluck('query')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
