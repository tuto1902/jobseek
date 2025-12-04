<?php

namespace App\Filament\Widgets;

use App\Models\JobPosting;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PopularCategoriesChartWidget extends ChartWidget
{
    protected ?string $heading = 'Most Popular Job Categories';

    protected function getData(): array
    {
        $data = JobPosting::select('category', DB::raw('COUNT(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Job Postings',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(201, 203, 207, 0.7)',
                        'rgba(255, 99, 71, 0.7)',
                    ],
                ],
            ],
            'labels' => $data->pluck('category')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
