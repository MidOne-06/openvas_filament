<?php

namespace App\Filament\Pages;

use App\Services\OpenVasApiClient;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AlertsPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Alertas';
    protected static ?string $navigationGroup = 'OpenVAS';
    protected static ?int    $navigationSort  = 6;
    protected static ?string $title           = 'Reglas de Alerta';
    protected static string  $view            = 'filament.pages.alerts';

    // Data
    public array  $rules   = [];
    public array  $history = [];
    public string $activeTab = 'rules';  // rules | history

    // Formulario nueva regla
    public bool   $showForm             = false;
    public string $form_name            = '';
    public string $form_description     = '';
    public float  $form_cvss_threshold  = 7.0;
    public string $form_severity_level  = '';
    public string $form_webhook_url     = '';
    public string $form_webhook_type    = 'generic';
    public string $form_email_to        = '';
    public string $form_task_ids        = '';
    public ?int   $form_new_vuln_count  = null;

    // Formulario test webhook
    public bool   $showTestForm      = false;
    public string $test_webhook_url  = '';
    public string $test_message      = 'Prueba de webhook desde OpenVAS Manager';

    public function mount(): void
    {
        $this->loadRules();
        $this->loadHistory();
    }

    public function loadRules(): void
    {
        try {
            $resp         = app(OpenVasApiClient::class)->getAlertRules();
            $this->rules  = $resp['rules'] ?? (is_array($resp) ? $resp : []);
        } catch (\Throwable $e) {
            $this->rules = [];
        }
    }

    public function loadHistory(): void
    {
        try {
            $resp           = app(OpenVasApiClient::class)->getAlertHistory(50);
            $this->history  = $resp['history'] ?? (is_array($resp) ? $resp : []);
        } catch (\Throwable $e) {
            $this->history = [];
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        if ($tab === 'history') {
            $this->loadHistory();
        }
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
    }

    public function createRule(): void
    {
        if (empty($this->form_name)) {
            Notification::make()->title('El nombre es obligatorio')->warning()->send();
            return;
        }
        try {
            $taskIds = ! empty($this->form_task_ids)
                ? array_map('trim', explode(',', $this->form_task_ids))
                : [];
            $data = [
                'name'           => $this->form_name,
                'description'    => $this->form_description    ?: null,
                'cvss_threshold' => $this->form_cvss_threshold > 0 ? $this->form_cvss_threshold : null,
                'severity_level' => $this->form_severity_level ?: null,
                'webhook_url'    => $this->form_webhook_url    ?: null,
                'webhook_type'   => $this->form_webhook_type   ?: 'generic',
                'email_to'       => $this->form_email_to       ?: null,
                'task_ids'       => $taskIds,
                'new_vuln_count' => $this->form_new_vuln_count ?: null,
            ];
            app(OpenVasApiClient::class)->createAlertRule($data);
            $this->showForm = false;
            $this->form_name = $this->form_description = '';
            $this->form_webhook_url = $this->form_email_to = $this->form_task_ids = '';
            $this->form_webhook_type = 'generic';
            $this->form_new_vuln_count = null;
            $this->form_cvss_threshold = 7.0;
            $this->loadRules();
            Notification::make()->title('Regla creada correctamente')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function toggleRule(string $ruleId): void
    {
        try {
            app(OpenVasApiClient::class)->toggleAlertRule($ruleId);
            $this->loadRules();
            Notification::make()->title('Estado de regla actualizado')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function deleteRule(string $ruleId): void
    {
        try {
            app(OpenVasApiClient::class)->deleteAlertRule($ruleId);
            $this->loadRules();
            Notification::make()->title('Regla eliminada')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function sendTestWebhook(): void
    {
        if (empty($this->test_webhook_url)) {
            Notification::make()->title('URL del webhook es obligatoria')->warning()->send();
            return;
        }
        try {
            $resp = app(OpenVasApiClient::class)->testWebhook([
                'url'     => $this->test_webhook_url,
                'message' => $this->test_message,
            ]);
            $ok = $resp['success'] ?? $resp['status'] === 'ok';
            if ($ok) {
                Notification::make()->title('Webhook enviado correctamente')->success()->send();
            } else {
                Notification::make()->title('Webhook fallo: ' . ($resp['error'] ?? 'Error desconocido'))->danger()->send();
            }
            $this->showTestForm = false;
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('nueva_regla')
                ->label('+ Nueva Regla')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->action('toggleForm'),

            Action::make('test_webhook')
                ->label('Probar Webhook')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->action(fn() => $this->showTestForm = ! $this->showTestForm),

            Action::make('refresh')
                ->label('Actualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn() => $this->loadRules() + $this->loadHistory()),
        ];
    }
}
