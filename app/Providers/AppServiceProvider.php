<?php

namespace App\Providers;

use App\Services\OpenVasApiClient;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpenVasApiClient::class, function () {
            return new OpenVasApiClient();
        });
    }

    public function boot(): void
    {
        // Registrar Chart.js globalmente en todos los paneles de Filament
        FilamentAsset::register([
            Js::make('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'),
        ]);
    }
}
