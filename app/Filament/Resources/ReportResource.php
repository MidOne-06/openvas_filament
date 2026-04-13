<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\OpenvasReport;
use App\Services\OpenVasApiClient;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model          = OpenvasReport::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $navigationGroup = 'OpenVAS';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel      = 'Reporte';
    protected static ?string $pluralModelLabel = 'Reportes';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('gvm_id')
                    ->label('ID Reporte')
                    ->limit(16)
                    ->tooltip(fn ($record) => $record->gvm_id)
                    ->copyable(),

                Tables\Columns\TextColumn::make('task_name')
                    ->label('Tarea')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('scan_start')
                    ->label('Inicio Escaneo')
                    ->sortable(),

                Tables\Columns\TextColumn::make('critical')
                    ->label('Critico')
                    ->badge()
                    ->color('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('high')
                    ->label('Alto')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('medium')
                    ->label('Medio')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('low')
                    ->label('Bajo')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_vulns')
                    ->label('Total')
                    ->sortable()
                    ->weight('bold'),
            ])
            ->actions([
                Tables\Actions\Action::make('kpis')
                    ->label('Ver KPIs')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->action(function (OpenvasReport $record) {
                        try {
                            $api  = app(OpenVasApiClient::class);
                            $kpis = $api->getReportKpis($record->gvm_id);
                            $record->update(['kpis' => $kpis, 'last_synced_at' => now()]);
                            Notification::make()->title('KPIs cargados')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger')
                    ->url(fn (OpenvasReport $r) => app(OpenVasApiClient::class)->getReportDownloadUrl($r->gvm_id, 'pdf'))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download_xml')
                    ->label('XML')
                    ->icon('heroicon-o-code-bracket')
                    ->color('gray')
                    ->url(fn (OpenvasReport $r) => app(OpenVasApiClient::class)->getReportDownloadUrl($r->gvm_id, 'xml'))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download_csv')
                    ->label('CSV')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->url(fn (OpenvasReport $r) => app(OpenVasApiClient::class)->getReportDownloadUrl($r->gvm_id, 'csv'))
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('sync_reports')
                    ->label('Sincronizar Reportes')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function () {
                        try {
                            $api   = app(OpenVasApiClient::class);
                            // GET /reports devuelve lista básica (sin conteos).
                            // Para obtener critical/high/medium/low hay que llamar
                            // a GET /reports/{id} por cada uno.
                            $list  = $api->getReports();
                            $synced = 0;
                            $errors = 0;

                            foreach ($list as $basicReport) {
                                try {
                                    $details = $api->getReport($basicReport['id']);
                                    if ($details) {
                                        OpenvasReport::syncFromApi($details);
                                        $synced++;
                                    }
                                } catch (\Throwable) {
                                    $errors++;
                                }
                            }

                            $msg = "{$synced} reportes sincronizados";
                            if ($errors > 0) $msg .= " ({$errors} con error)";

                            Notification::make()->title($msg)->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->defaultSort('scan_start', 'desc')
            ->emptyStateHeading('No hay reportes sincronizados')
            ->emptyStateDescription('Haz clic en "Sincronizar Reportes" para cargar desde OpenVAS.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view'  => Pages\ViewReport::route('/{record}'),
        ];
    }
}
