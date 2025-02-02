<?php

namespace App\Filament\Widgets;

use App\Models\Result;
use App\Settings\GeneralSettings;
use Filament\Widgets\LineChartWidget;

class RecentJitterChart extends LineChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = '24h';

    protected function getHeading(): string
    {
        return 'Jitter (ms)';
    }

    protected function getFilters(): ?array
    {
        return [
            '24h' => 'Last 24h',
            'week' => 'Last week',
            'month' => 'Last month',
        ];
    }

    protected function getData(): array
    {
        $settings = new GeneralSettings();

        $results = Result::query()
            ->select(['data', 'created_at'])
            ->when($this->filter == '24h', function ($query) {
                $query->where('created_at', '>=', now()->subDay());
            })
            ->when($this->filter == 'week', function ($query) {
                $query->where('created_at', '>=', now()->subWeek());
            })
            ->when($this->filter == 'month', function ($query) {
                $query->where('created_at', '>=', now()->subMonth());
            })
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Download',
                    'data' => $results->map(fn ($item) => $item->getJitterData()['download']),
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => '#0ea5e9',
                    'fill' => false,
                    'cubicInterpolationMode' => 'monotone',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Upload',
                    'data' => $results->map(fn ($item) => $item->getJitterData()['upload']),
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => '#8b5cf6',
                    'fill' => false,
                    'cubicInterpolationMode' => 'monotone',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Ping',
                    'data' => $results->map(fn ($item) => $item->getJitterData()['ping']),
                    'borderColor' => '#10b981',
                    'backgroundColor' => '#10b981',
                    'fill' => false,
                    'cubicInterpolationMode' => 'monotone',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $results->map(fn ($item) => $item->created_at->timezone($settings->timezone)->format('M d - G:i')),
        ];
    }
}
