<?php

namespace App\Filament\Pages;

use App\Services\OpenVasApiClient;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ConectividadPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-wifi';
    protected static ?string $navigationLabel = 'Conectividad';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?int    $navigationSort  = 10;
    protected static ?string $title           = 'Conectividad con OpenVAS';
    protected static string  $view            = 'filament.pages.conectividad';

    public bool   $checked       = false;
    public bool   $connected     = false;
    public bool   $authenticated = false;
    public string $vmInfo        = '';
    public ?float $latencyMs     = null;
    public string $openvasVersion = '';
    public string $errorMessage  = '';
    public array  $history       = [];
    public array  $versionInfo   = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('check')
                ->label('Verificar Conexion')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action('checkConnectivity'),
        ];
    }

    public function checkConnectivity(): void
    {
        try {
            $api    = app(OpenVasApiClient::class);
            $report = $api->connectivity();

            $this->checked        = true;
            $this->connected      = $report['ok'] ?? false;
            $this->vmInfo         = $report['vm'] ?? '';
            $this->latencyMs      = $report['tcp']['latency_ms'] ?? null;
            $this->authenticated  = $report['gmp']['authenticated'] ?? false;
            $this->openvasVersion = $report['gmp']['openvas_version'] ?? '';
            $this->errorMessage   = $report['gmp']['error'] ?? $report['tcp']['error'] ?? '';

            try {
                $this->versionInfo = $api->version();
                $this->history     = $api->history(15);
            } catch (\Throwable) {
                // Non-critical
            }

            Notification::make()
                ->title($this->connected ? 'Conexion establecida con OpenVAS' : 'No se pudo conectar a OpenVAS')
                ->{$this->connected ? 'success' : 'danger'}()
                ->send();

        } catch (\Throwable $e) {
            $this->checked      = true;
            $this->connected    = false;
            $this->errorMessage = $e->getMessage();
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }
}
