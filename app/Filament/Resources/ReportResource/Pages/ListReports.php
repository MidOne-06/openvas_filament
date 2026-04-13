<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Filament\Widgets\ReportSeverityWidget;
use App\Filament\Widgets\ReportTopTasksWidget;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ReportSeverityWidget::class,
            ReportTopTasksWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
