<?php

namespace App\Filament\Widgets;

use App\Models\OpenvasReport;
use Filament\Widgets\ChartWidget;

class ReportSeverityWidget extends ChartWidget
{
    protected static ?string $heading   = 'Distribucion por Severidad';
    protected static ?int    $sort      = 3;
    protected static string  $color     = 'danger';
    protected static ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return true; // Show on dashboard AND on reports page
    }

    protected function getData(): array
    {
        $t = OpenvasReport::selectRaw(
            'SUM(critical) as c, SUM(high) as h, SUM(medium) as m, SUM(low) as l'
        )->first();

        $c = (int) ($t->c ?? 0);
        $h = (int) ($t->h ?? 0);
        $m = (int) ($t->m ?? 0);
        $l = (int) ($t->l ?? 0);

        return [
            'datasets' => [[
                'data'            => [$c, $h, $m, $l],
                'backgroundColor' => ['#dc2626', '#ea580c', '#d97706', '#65a30d'],
                'hoverOffset'     => 8,
            ]],
            'labels' => [
                "Critico ({$c})",
                "Alto ({$h})",
                "Medio ({$m})",
                "Bajo ({$l})",
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'cutout'  => '60%',
        ];
    }
}
