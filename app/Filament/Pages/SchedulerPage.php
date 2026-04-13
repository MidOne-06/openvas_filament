<?php

namespace App\Filament\Pages;

use App\Services\OpenVasApiClient;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SchedulerPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Programador';
    protected static ?string $navigationGroup = 'OpenVAS';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $title           = 'Programacion de Escaneos';
    protected static string  $view            = 'filament.pages.scheduler';

    // Lista de tareas
    public array $jobs = [];

    // Datos de OpenVAS para los selectores del formulario
    public array $scanConfigs = [];   // [["id"=>uuid, "name"=>nombre], ...]
    public array $portLists   = [];   // [["id"=>uuid, "name"=>nombre], ...]

    // Formulario de creacion
    public bool   $showForm            = false;
    public string $form_name           = '';
    public string $form_task_id        = '';
    public string $form_hosts          = '';
    public string $form_scan_config_id = '';
    public string $form_port_list_id   = '';
    public string $form_schedule_type  = 'daily';
    public int    $form_hour           = 2;
    public int    $form_minute         = 0;
    public string $form_day_of_week    = 'mon';
    public int    $form_day_of_month   = 1;
    public string $form_run_date       = '';
    public string $form_cron           = '0 2 * * *';
    public string $form_notify_webhook = '';

    public function mount(): void
    {
        $this->loadJobs();
    }

    public function loadJobs(): void
    {
        try {
            $api        = app(OpenVasApiClient::class);
            $resp       = $api->getSchedulerJobs();
            $this->jobs = $resp['jobs'] ?? (is_array($resp) ? array_values($resp) : []);
        } catch (\Throwable $e) {
            $this->jobs = [];
            Notification::make()->title('Error cargando tareas: ' . $e->getMessage())->danger()->send();
        }
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;

        // Cargar configs y port-lists la primera vez que se abre el formulario
        if ($this->showForm && empty($this->scanConfigs)) {
            $this->loadOpenVasOptions();
        }
    }

    public function loadOpenVasOptions(): void
    {
        $api = app(OpenVasApiClient::class);

        try {
            $raw = $api->getConfigs();
            // Puede venir como lista directa o como {"configs":[...]}
            $this->scanConfigs = isset($raw[0]) ? $raw : ($raw['configs'] ?? $raw['scan_configs'] ?? []);
        } catch (\Throwable) {
            $this->scanConfigs = [];
        }

        try {
            $raw = $api->getPortLists();
            $this->portLists = isset($raw[0]) ? $raw : ($raw['port_lists'] ?? []);
        } catch (\Throwable) {
            $this->portLists = [];
        }
    }

    public function createJob(): void
    {
        if (empty($this->form_name) || empty($this->form_hosts) || empty($this->form_scan_config_id)) {
            Notification::make()->title('Nombre, hosts y Configuracion de Escaneo son obligatorios')->warning()->send();
            return;
        }

        try {
            $hostsArray = array_values(array_filter(array_map('trim', explode(',', $this->form_hosts))));

            $data = [
                'task_name'       => $this->form_name,
                'target_hosts'    => $hostsArray,
                'scan_config_id'  => $this->form_scan_config_id,
                'schedule_type'   => $this->form_schedule_type,
                'hour'            => $this->form_hour,
                'minute'          => $this->form_minute,
                'cron_expression' => $this->form_cron,
                'notify_webhook'  => $this->form_notify_webhook ?: null,
            ];

            if (!empty($this->form_task_id))       $data['task_id']        = $this->form_task_id;
            if (!empty($this->form_port_list_id))  $data['port_list_id']   = $this->form_port_list_id;
            if (!empty($this->form_day_of_week))   $data['day_of_week']    = $this->form_day_of_week;
            if ($this->form_day_of_month > 0)      $data['day_of_month']   = $this->form_day_of_month;
            if (!empty($this->form_run_date))       $data['run_date']       = $this->form_run_date;

            app(OpenVasApiClient::class)->createSchedulerJob($data);

            // Limpiar formulario
            $this->showForm = false;
            $this->form_name = $this->form_task_id = $this->form_hosts = '';
            $this->form_scan_config_id = $this->form_port_list_id = '';
            $this->form_notify_webhook = $this->form_run_date = '';
            $this->form_cron = '0 2 * * *';
            $this->form_hour = 2; $this->form_minute = 0;

            $this->loadJobs();
            Notification::make()->title('Tarea programada creada correctamente')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function pauseJob(string $jobId): void
    {
        try {
            app(OpenVasApiClient::class)->pauseSchedulerJob($jobId);
            $this->loadJobs();
            Notification::make()->title('Tarea pausada')->warning()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function resumeJob(string $jobId): void
    {
        try {
            app(OpenVasApiClient::class)->resumeSchedulerJob($jobId);
            $this->loadJobs();
            Notification::make()->title('Tarea reanudada')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function runNow(string $jobId): void
    {
        try {
            app(OpenVasApiClient::class)->runNowSchedulerJob($jobId);
            $this->loadJobs();
            Notification::make()->title('Escaneo iniciado inmediatamente')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function deleteJob(string $jobId): void
    {
        try {
            app(OpenVasApiClient::class)->deleteSchedulerJob($jobId);
            $this->loadJobs();
            Notification::make()->title('Tarea eliminada')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('nueva_tarea')
                ->label('+ Nueva Tarea')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->action('toggleForm'),

            Action::make('actualizar')
                ->label('Actualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('loadJobs'),
        ];
    }
}
