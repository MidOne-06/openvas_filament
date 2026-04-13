<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\OpenvasTask;
use App\Services\OpenVasApiClient;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Informacion de la Tarea')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('name')->label('Nombre'),
                        TextEntry::make('status')->label('Estado')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor()),
                        TextEntry::make('progress')->label('Progreso')->suffix('%'),
                        TextEntry::make('gvm_id')->label('ID OpenVAS'),
                        TextEntry::make('last_report_id')->label('Ultimo Reporte ID'),
                        TextEntry::make('last_synced_at')->label('Ultima Sincronizacion')->dateTime('d/m/Y H:i'),
                    ]),
                ]),

            Section::make('KPIs del Ultimo Reporte')
                ->visible(fn (OpenvasTask $record) => ! empty($record->kpis))
                ->schema([
                    Grid::make(5)->schema([
                        TextEntry::make('kpis.totals.critical')->label('Critico')->color('danger'),
                        TextEntry::make('kpis.totals.high')->label('Alto')->color('warning'),
                        TextEntry::make('kpis.totals.medium')->label('Medio')->color('info'),
                        TextEntry::make('kpis.totals.low')->label('Bajo')->color('success'),
                        TextEntry::make('kpis.totals.total')->label('Total')->weight('bold'),
                    ]),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh_kpis')
                ->label('Actualizar KPIs')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $record = $this->getRecord();
                    try {
                        $api     = app(OpenVasApiClient::class);
                        $metrics = $api->getTaskMetrics($record->gvm_id);
                        $record->update([
                            'status'         => $metrics['status'] ?? $record->status,
                            'progress'       => $metrics['progress'] ?? $record->progress,
                            'last_report_id' => $metrics['last_report_id'] ?? $record->last_report_id,
                            'kpis'           => $metrics['kpis'] ?? $record->kpis,
                            'last_synced_at' => now(),
                        ]);
                        Notification::make()->title('KPIs actualizados')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
