<?php

namespace App\Filament\Widgets;

use App\Models\OpenvasReport;
use Filament\Widgets\ChartWidget;

class ReportTopTasksWidget extends ChartWidget
{
    protected static ?string $heading   = 'Top Tareas por Vulnerabilidades';
    protected static ?int    $sort      = 4;
    protected static string  $color     = 'warning';
    protected static ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return true;
    }

    protected function getData(): array
    {
        $rows = OpenvasReport::selectRaw(
            'task_name,
             SUM(critical) as total_critical,
             SUM(high)     as total_high,
             SUM(medium)   as total_medium,
             SUM(low)      as total_low'
        )
            ->groupBy('task_name')
            ->orderByRaw('SUM(critical) + SUM(high) DESC')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Critico',
                    'data'            => $rows->pluck('total_critical')->map(fn ($v) => (int) $v)->toArray(),
                    'backgroundColor' => '#dc2626',
                ],
                [
                    'label'           => 'Alto',
                    'data'            => $rows->pluck('total_high')->map(fn ($v) => (int) $v)->toArray(),
                    'backgroundColor' => '#ea580c',
                ],
                [
                    'label'           => 'Medio',
                    'data'            => $rows->pluck('total_medium')->map(fn ($v) => (int) $v)->toArray(),
                    'backgroundColor' => '#d97706',
                ],
            ],
            'labels' => $rows->pluck('task_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales'    => [
                'x' => ['stacked' => false, 'beginAtZero' => true],
                'y' => ['stacked' => false],
            ],
            'plugins' => ['legend' => ['position' => 'top']],
        ];
    }
}
