<?php

namespace App\Filament\Pages;

use App\Services\OpenVasApiClient;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DiffPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Comparar Reportes';
    protected static ?string $navigationGroup = 'Analisis';
    protected static ?int    $navigationSort  = 8;
    protected static ?string $title           = 'Comparacion de Reportes (Diff)';
    protected static string  $view            = 'filament.pages.diff';

    public string $report_id_a = '';
    public string $report_id_b = '';
    public array  $result      = [];
    public bool   $compared    = false;
    public string $diffPdfUrl  = '';
    public string $diffExcelUrl = '';

    public function compare(): void
    {
        if (empty($this->report_id_a) || empty($this->report_id_b)) {
            Notification::make()->title('Introduce ambos IDs de reporte')->warning()->send();
            return;
        }
        if ($this->report_id_a === $this->report_id_b) {
            Notification::make()->title('Los dos reportes deben ser diferentes')->warning()->send();
            return;
        }
        try {
            $api           = app(OpenVasApiClient::class);
            $this->result  = $api->getReportDiff($this->report_id_a, $this->report_id_b);
            $this->diffPdfUrl   = $api->getReportDiffPdfUrl($this->report_id_a, $this->report_id_b);
            $this->diffExcelUrl = $api->getReportDiffExcelUrl($this->report_id_a, $this->report_id_b);
            $this->compared = true;
            Notification::make()->title('Comparacion completada')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function clearDiff(): void
    {
        $this->report_id_a = $this->report_id_b = '';
        $this->result      = [];
        $this->compared    = false;
        $this->diffPdfUrl  = $this->diffExcelUrl = '';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('limpiar')
                ->label('Limpiar')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->action('clearDiff'),
        ];
    }
}
