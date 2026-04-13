<?php

namespace App\Filament\Resources\CsvReportResource\Pages;

use App\Filament\Resources\CsvReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCsvReports extends ListRecords
{
    protected static string $resource = CsvReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_csv_list')
                ->label('Sincronizar lista')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () {
                    try {
                        $api   = app(\App\Services\OpenVasApiClient::class);
                        $items = $api->getCsvReports(200);
                        $synced = 0;

                        foreach ($items as $item) {
                            $rid = $item['report_id'] ?? null;
                            if (! $rid) continue;

                            // Obtener métricas completas para este report_id
                            $metrics = $api->getCsvMetrics($rid);
                            $dist    = $metrics['severity_distribution'] ?? [];

                            \App\Models\CsvReport::updateOrCreate(
                                ['report_id' => $rid],
                                [
                                    'total_vulns'  => (int) ($metrics['total_vulnerabilities'] ?? $item['total_vulns'] ?? 0),
                                    'critical'     => (int) ($dist['critical'] ?? $item['critical_count'] ?? 0),
                                    'high'         => (int) ($dist['high'] ?? $item['high_count'] ?? 0),
                                    'medium'       => (int) ($dist['medium'] ?? $item['medium_count'] ?? 0),
                                    'low'          => (int) ($dist['low'] ?? $item['low_count'] ?? 0),
                                    'unique_hosts' => (int) ($metrics['unique_hosts'] ?? $item['unique_hosts'] ?? 0),
                                    'metrics'      => $metrics,
                                ]
                            );
                            $synced++;
                        }

                        \Filament\Notifications\Notification::make()
                            ->title("{$synced} analisis CSV sincronizados")
                            ->success()->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error: ' . $e->getMessage())
                            ->danger()->send();
                    }
                }),

            Actions\Action::make('upload')
                ->label('Subir CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->url(CsvReportResource::getUrl('upload')),
        ];
    }
}
