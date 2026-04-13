<?php

namespace App\Filament\Widgets;

use App\Services\OpenVasApiClient;
use Filament\Widgets\Widget;

class ConnectivityWidget extends Widget
{
    protected static ?int    $sort      = 2;
    protected static string  $view      = 'filament.widgets.connectivity-widget';
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false; // Solo se muestra en la pagina de Conectividad
    }

    public bool   $connected     = false;
    public string $vmInfo        = '';
    public ?float $latencyMs     = null;
    public bool   $authenticated = false;
    public string $openvasVersion = '';
    public string $errorMessage  = '';
    public bool   $checked       = false;

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
        } catch (\Throwable $e) {
            $this->checked      = true;
            $this->connected    = false;
            $this->errorMessage = $e->getMessage();
        }
    }
}
