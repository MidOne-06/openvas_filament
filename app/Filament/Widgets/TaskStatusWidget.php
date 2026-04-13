<?php

namespace App\Filament\Widgets;

use App\Models\OpenvasTask;
use Filament\Widgets\ChartWidget;

class TaskStatusWidget extends ChartWidget
{
    protected static ?string $heading   = 'Estado de Tareas';
    protected static ?int    $sort      = 2;
    protected static string  $color     = 'info';
    protected static ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return OpenvasTask::exists();
    }

    protected function getData(): array
    {
        $statuses = OpenvasTask::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $colorMap = [
            'Done'        => '#10b981',
            'Running'     => '#3b82f6',
            'Stopped'     => '#f59e0b',
            'New'         => '#6b7280',
            'Interrupted' => '#ef4444',
            'Requested'   => '#8b5cf6',
        ];

        $labels = array_keys($statuses);
        $data   = array_values($statuses);
        $colors = array_map(fn ($s) => $colorMap[$s] ?? '#9ca3af', $labels);

        return [
            'datasets' => [[
                'data'            => $data,
                'backgroundColor' => $colors,
                'hoverOffset'     => 6,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['boxWidth' => 12]]],
        ];
    }
}
