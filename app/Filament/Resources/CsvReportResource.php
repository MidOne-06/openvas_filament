<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CsvReportResource\Pages;
use App\Models\CsvReport;
use App\Services\OpenVasApiClient;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CsvReportResource extends Resource
{
    protected static ?string $model           = CsvReport::class;
    protected static ?string $navigationIcon  = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Analisis CSV';
    protected static ?string $navigationGroup = 'OpenVAS';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $modelLabel      = 'Analisis CSV';
    protected static ?string $pluralModelLabel = 'Analisis CSV';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_id')
                    ->label('ID')
                    ->searchable()
                    ->copyable()
                    ->limit(28),

                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Archivo')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('critical')
                    ->label('Critico')->badge()->color('danger'),

                Tables\Columns\TextColumn::make('high')
                    ->label('Alto')->badge()->color('warning'),

                Tables\Columns\TextColumn::make('medium')
                    ->label('Medio')->badge()->color('info'),

                Tables\Columns\TextColumn::make('low')
                    ->label('Bajo')->badge()->color('success'),

                Tables\Columns\TextColumn::make('total_vulns')
                    ->label('Total')->sortable()->weight('bold'),

                Tables\Columns\TextColumn::make('unique_hosts')
                    ->label('Hosts'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                // ── Reporte Profesional (modal) ──────────────────────────────
                Tables\Actions\Action::make('professional_report')
                    ->label('Reporte Completo')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->modalHeading(fn (CsvReport $r) => 'Reporte Profesional: ' . $r->original_filename)
                    ->modalWidth('7xl')
                    ->modalContent(function (CsvReport $record): \Illuminate\Contracts\View\View {
                        $api      = app(OpenVasApiClient::class);
                        $metrics  = $api->getCsvMetrics($record->report_id);
                        $rowsData = $api->getCsvRows($record->report_id);
                        return view('filament.modals.csv-professional-report', [
                            'record'  => $record,
                            'metrics' => $metrics,
                            'rows'    => $rowsData['rows'] ?? [],
                            'pdfUrl'  => $api->getCsvPdfUrl($record->report_id),
                            'apiBase' => $api->getBaseUrl(),
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                // ── Descargar Excel ──────────────────────────────────────────
                Tables\Actions\Action::make('excel')
                    ->label('Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (CsvReport $r) => app(OpenVasApiClient::class)->getCsvExcelUrl($r->report_id))
                    ->openUrlInNewTab(),

                // ── Borrar ───────────────────────────────────────────────────
                Tables\Actions\DeleteAction::make()
                    ->action(function (CsvReport $record) {
                        try {
                            app(OpenVasApiClient::class)->deleteCsvReport($record->report_id);
                            $record->delete();
                            Notification::make()->title('Analisis eliminado')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No hay analisis CSV')
            ->emptyStateDescription('Sube un CSV exportado desde OpenVAS para analizarlo.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCsvReports::route('/'),
            'upload' => Pages\UploadCsv::route('/upload'),
        ];
    }
}
