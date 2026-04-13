<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\OpenvasReport;
use App\Services\OpenVasApiClient;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Section::make('Resumen del Reporte')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('gvm_id')
                            ->label('ID OpenVAS')
                            ->copyable()
                            ->columnSpan(2),
                        TextEntry::make('task_name')->label('Tarea'),
                        TextEntry::make('task_id')->label('Task ID'),
                        TextEntry::make('scan_start')->label('Inicio Escaneo'),
                        TextEntry::make('scan_end')->label('Fin Escaneo'),
                        TextEntry::make('last_synced_at')
                            ->label('Ultima sincronizacion')
                            ->since(),
                    ]),
                ]),

            Section::make('Vulnerabilidades por Severidad')
                ->schema([
                    Grid::make(5)->schema([
                        TextEntry::make('critical')
                            ->label('Critico')
                            ->badge()
                            ->color('danger'),
                        TextEntry::make('high')
                            ->label('Alto')
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('medium')
                            ->label('Medio')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('low')
                            ->label('Bajo')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('total_vulns')
                            ->label('TOTAL')
                            ->badge()
                            ->color('gray')
                            ->weight('bold'),
                    ]),
                ]),

            Section::make('Graficos y KPIs Detallados')
                ->description('Presiona "Recargar KPIs" para obtener datos completos desde OpenVAS.')
                ->collapsed(false)
                ->schema([
                    ViewEntry::make('kpis')
                        ->label('')
                        ->view('filament.infolists.report-charts'),
                ])
                ->visible(fn (OpenvasReport $record) => ! empty($record->kpis)),

            Section::make('Top 10 Hosts Afectados')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('kpis.hosts.unique')
                            ->label('Total Hosts Unicos'),
                        TextEntry::make('kpis.nvts.unique')
                            ->label('NVTs Unicos'),
                        TextEntry::make('kpis.cves.unique')
                            ->label('CVEs Unicos'),
                    ]),
                    ViewEntry::make('kpis.hosts.top')
                        ->label('Hosts con mas vulnerabilidades')
                        ->view('filament.infolists.top-hosts-table'),
                ])
                ->visible(fn (OpenvasReport $record) => ! empty($record->kpis['hosts']['top'])),

        ]);
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $api    = app(OpenVasApiClient::class);

        return [
            Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-document')
                ->color('danger')
                ->url($api->getReportDownloadUrl($record->gvm_id, 'pdf'))
                ->openUrlInNewTab(),

            Actions\Action::make('xml')
                ->label('XML')
                ->icon('heroicon-o-code-bracket')
                ->color('gray')
                ->url($api->getReportDownloadUrl($record->gvm_id, 'xml'))
                ->openUrlInNewTab(),

            Actions\Action::make('csv')
                ->label('CSV')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url($api->getReportDownloadUrl($record->gvm_id, 'csv'))
                ->openUrlInNewTab(),

            Actions\Action::make('refresh')
                ->label('Recargar KPIs')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () use ($record, $api) {
                    try {
                        $kpis = $api->getReportKpis($record->gvm_id);
                        $record->update(['kpis' => $kpis, 'last_synced_at' => now()]);
                        Notification::make()
                            ->title('KPIs actualizados — ' . ($kpis['totals']['total'] ?? 0) . ' resultados')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
