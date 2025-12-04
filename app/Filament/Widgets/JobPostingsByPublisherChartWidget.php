<?php

namespace App\Filament\Widgets;

use App\Models\Publisher;
use Filament\Widgets\ChartWidget;

class JobPostingsByPublisherChartWidget extends ChartWidget
{
    protected ?string $heading = 'Job Postings by Publisher';

    protected function getData(): array
    {
        $data = Publisher::withCount('jobPostings')
            ->orderBy('job_postings_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Job Postings',
                    'data' => $data->pluck('job_postings_count')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(201, 203, 207, 0.7)',
                        'rgba(255, 99, 71, 0.7)',
                        'rgba(144, 238, 144, 0.7)',
                        'rgba(173, 216, 230, 0.7)',
                    ],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
