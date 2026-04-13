<?php

namespace App\Filament\Widgets;

use App\Models\CsvReport;
use App\Models\OpenvasReport;
use App\Models\OpenvasTask;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalTasks   = OpenvasTask::count();
        $running      = OpenvasTask::where('status', 'Running')->count();
        $done         = OpenvasTask::where('status', 'Done')->count();
        $totalReports = OpenvasReport::count();
        $totalVulns   = OpenvasReport::sum('total_vulns');
        $critical     = OpenvasReport::sum('critical');

        return [
            Stat::make('Tareas Totales', $totalTasks)
                ->description("{$running} en ejecucion · {$done} completadas")
                ->color('primary')
                ->icon('heroicon-o-cpu-chip'),

            Stat::make('Reportes', $totalReports)
                ->description('Sincronizados desde OpenVAS')
                ->color('info')
                ->icon('heroicon-o-document-chart-bar'),

            Stat::make('Total Vulnerabilidades', number_format($totalVulns))
                ->description("{$critical} criticas detectadas")
                ->color($critical > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-shield-exclamation'),

            Stat::make('Analisis CSV', CsvReport::count())
                ->description('Archivos CSV procesados')
                ->color('warning')
                ->icon('heroicon-o-table-cells'),
        ];
    }
}
