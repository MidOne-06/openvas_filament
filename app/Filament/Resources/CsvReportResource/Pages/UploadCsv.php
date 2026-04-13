<?php

namespace App\Filament\Resources\CsvReportResource\Pages;

use App\Filament\Resources\CsvReportResource;
use App\Models\CsvReport;
use App\Services\OpenVasApiClient;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;

class UploadCsv extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = CsvReportResource::class;
    protected static string $view     = 'filament.pages.upload-csv';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('csv_file')
                    ->label('Archivo CSV de OpenVAS')
                    ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain'])
                    ->required()
                    ->maxSize(20480) // 20 MB
                    ->disk('local')
                    ->directory('csv-uploads'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $formData = $this->form->getState();
        $filePath = $formData['csv_file'];

        try {
            $api      = app(OpenVasApiClient::class);
            $fullPath = Storage::disk('local')->path($filePath);
            $filename = basename($filePath);

            // 1. Subir y procesar el CSV en el backend
            $result   = $api->uploadCsv($fullPath, $filename);
            $reportId = $result['report_id'] ?? null;
            $uploadStats = $result['stats'] ?? [];

            if (! $reportId) {
                Notification::make()->title('Error: el backend no retorno un report_id')->danger()->send();
                return;
            }

            // 2. Obtener métricas de severidad — process_csv() solo retorna
            //    {total_rows, inserted, errors}. Los conteos reales están en
            //    GET /csv/metrics/{id}  →  severity_distribution, unique_hosts, etc.
            $metrics = $api->getCsvMetrics($reportId);
            $dist    = $metrics['severity_distribution'] ?? [];

            CsvReport::create([
                'report_id'         => $reportId,
                'original_filename' => $filename,
                'total_vulns'       => (int) ($metrics['total_vulnerabilities'] ?? $uploadStats['inserted'] ?? 0),
                'critical'          => (int) ($dist['critical'] ?? 0),
                'high'              => (int) ($dist['high'] ?? 0),
                'medium'            => (int) ($dist['medium'] ?? 0),
                'low'               => (int) ($dist['low'] ?? 0),
                'info_count'        => 0,
                'unique_hosts'      => (int) ($metrics['unique_hosts'] ?? 0),
                'metrics'           => $metrics,
            ]);

            Storage::disk('local')->delete($filePath);

            $total = $metrics['total_vulnerabilities'] ?? $uploadStats['inserted'] ?? 0;
            Notification::make()
                ->title("CSV procesado — {$total} vulnerabilidades encontradas")
                ->success()->send();

            $this->redirect(CsvReportResource::getUrl('index'));

        } catch (\Throwable $e) {
            Notification::make()->title('Error al procesar CSV: ' . $e->getMessage())->danger()->send();
        }
    }
}
