<?php

namespace App\Filament\Pages;

use App\Services\OpenVasApiClient;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TrendsPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Tendencias';
    protected static ?string $navigationGroup = 'Analisis';
    protected static ?int    $navigationSort  = 7;
    protected static ?string $title           = 'Dashboard de Tendencias';
    protected static string  $view            = 'filament.pages.trends';

    public array  $globalTrends = [];
    public array  $hostTrends   = [];
    public array  $summary      = [];
    public bool   $loaded       = false;

    public function mount(): void
    {
        $this->loadTrends();
    }

    public function loadTrends(): void
    {
        try {
            $api = app(OpenVasApiClient::class);

            $global           = $api->getGlobalTrends();
            // Backend returns: {period_days, total_scans, timeline:[{date,total,critical,...}], ...}
            $this->globalTrends = $global['timeline'] ?? [];
            $this->summary      = $global; // keeps total_scans, period_days, etc.

            $hosts            = $api->getHostTrends();
            $this->hostTrends = $hosts['hosts']  ?? $hosts['data'] ?? (is_array($hosts) ? $hosts : []);

            $this->loaded = true;
        } catch (\Throwable $e) {
            Notification::make()->title('Error cargando tendencias: ' . $e->getMessage())->danger()->send();
        }
    }

    public function backfill(): void
    {
        try {
            app(OpenVasApiClient::class)->backfillTrends();
            $this->loadTrends();
            Notification::make()->title('Backfill completado — metricas rellenadas')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function backfillCsv(): void
    {
        try {
            $resp = app(OpenVasApiClient::class)->backfillTrendsCsv();
            $this->loadTrends();
            $copied  = $resp['copied']  ?? 0;
            $skipped = $resp['skipped'] ?? 0;
            Notification::make()
                ->title("Backfill CSV completado: {$copied} insertados, {$skipped} ya existian")
                ->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Actualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action('loadTrends'),

            Action::make('backfill_csv')
                ->label('Backfill desde CSV')
                ->icon('heroicon-o-server-stack')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Rellenar metricas desde reportes CSV')
                ->modalDescription('Procesara todos los reportes CSV ya subidos para poblar el historial de tendencias.')
                ->action('backfillCsv'),

            Action::make('backfill')
                ->label('Backfill desde GVM')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Rellenar metricas desde escaneos GVM')
                ->modalDescription('Copia datos de escaneos GVM historicos. Solo funciona si tienes escaneos ejecutados con el backend GVM.')
                ->action('backfill'),
        ];
    }
}
