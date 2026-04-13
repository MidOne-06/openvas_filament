<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente HTTP para el backend OpenVAS (FastAPI).
 * Mapea todos los endpoints disponibles en el backend.
 */
class OpenVasApiClient
{
    private string $baseUrl;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('openvas.api_url', env('OPENVAS_API_URL', 'http://localhost:8000')), '/');
        $this->timeout = (int) config('openvas.timeout', 30);
    }

    // -------------------------------------------------------------------------
    // Acceso público al base URL (para pasarlo a las vistas Blade)
    // -------------------------------------------------------------------------

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function get(string $path, array $query = []): array
    {
        $response = Http::timeout($this->timeout)
            ->get("{$this->baseUrl}{$path}", $query);

        $this->logIfError($response, $path);
        return $response->json() ?? [];
    }

    private function post(string $path, array $data = []): array
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->baseUrl}{$path}", $data);

        $this->logIfError($response, $path);
        return $response->json() ?? [];
    }

    private function delete(string $path, array $query = []): array
    {
        $response = Http::timeout($this->timeout)
            ->delete("{$this->baseUrl}{$path}", $query);

        $this->logIfError($response, $path);
        return $response->json() ?? [];
    }

    private function logIfError(Response $response, string $path): void
    {
        if ($response->failed()) {
            Log::error("OpenVAS API error [{$path}]", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Health & conectividad
    // -------------------------------------------------------------------------

    /** GET /health */
    public function health(): array
    {
        return $this->get('/health');
    }

    /** GET /connectivity — TCP + GMP auth check */
    public function connectivity(): array
    {
        return $this->get('/connectivity');
    }

    /** GET /version — versión de OpenVAS y GMP */
    public function version(): array
    {
        return $this->get('/version');
    }

    /** GET /history */
    public function history(int $limit = 10): array
    {
        return $this->get('/history', ['limit' => $limit]);
    }

    // -------------------------------------------------------------------------
    // Configuraciones y Port Lists
    // -------------------------------------------------------------------------

    /** GET /configs */
    public function getConfigs(): array
    {
        return $this->get('/configs');
    }

    /** GET /port-lists */
    public function getPortLists(): array
    {
        return $this->get('/port-lists');
    }

    /** POST /port-lists/custom */
    public function createCustomPortList(string $ports): array
    {
        $response = Http::timeout($this->timeout)
            ->withBody($ports, 'application/json')
            ->post("{$this->baseUrl}/port-lists/custom");

        return $response->json() ?? [];
    }

    // -------------------------------------------------------------------------
    // Targets
    // -------------------------------------------------------------------------

    /**
     * POST /targets
     * @param string|array $hosts  IP, rango o array de IPs
     */
    public function createTarget(string $name, string|array $hosts, ?string $portListId = null, ?string $customPorts = null): array
    {
        $payload = ['name' => $name, 'hosts' => $hosts];
        if ($portListId)  $payload['port_list_id'] = $portListId;
        if ($customPorts) $payload['custom_ports']  = $customPorts;

        return $this->post('/targets', $payload);
    }

    // -------------------------------------------------------------------------
    // Tasks
    // -------------------------------------------------------------------------

    /** GET /tasks */
    public function getTasks(): array
    {
        return $this->get('/tasks');
    }

    /** POST /tasks */
    public function createTask(string $name, string $configId, string $targetId): array
    {
        return $this->post('/tasks', [
            'name'      => $name,
            'config_id' => $configId,
            'target_id' => $targetId,
        ]);
    }

    /** GET /tasks/{task_id} */
    public function getTask(string $taskId): array
    {
        return $this->get("/tasks/{$taskId}");
    }

    /** GET /tasks/{task_id}/metrics */
    public function getTaskMetrics(string $taskId): array
    {
        return $this->get("/tasks/{$taskId}/metrics");
    }

    /** GET /tasks/{task_id}/summary */
    public function getTaskSummary(string $taskId): array
    {
        return $this->get("/tasks/{$taskId}/summary");
    }

    /** POST /tasks/{task_id}/start */
    public function startTask(string $taskId): array
    {
        return $this->post("/tasks/{$taskId}/start");
    }

    /** POST /tasks/{task_id}/stop */
    public function stopTask(string $taskId): array
    {
        return $this->post("/tasks/{$taskId}/stop");
    }

    /** POST /tasks/{task_id}/resume */
    public function resumeTask(string $taskId): array
    {
        return $this->post("/tasks/{$taskId}/resume");
    }

    /** DELETE /tasks/{task_id} */
    public function deleteTask(string $taskId, bool $ultimate = false): array
    {
        return $this->delete("/tasks/{$taskId}", ['ultimate' => $ultimate ? 'true' : 'false']);
    }

    /** POST /tasks/{task_id}/clone */
    public function cloneTask(string $taskId, ?string $name = null): array
    {
        return $this->post("/tasks/{$taskId}/clone", $name ? ['name' => $name] : []);
    }

    // -------------------------------------------------------------------------
    // Reports
    // -------------------------------------------------------------------------

    /** GET /reports */
    public function getReports(?string $taskFilter = null): array
    {
        $query = $taskFilter ? ['task_filter' => $taskFilter] : [];
        return $this->get('/reports', $query);
    }

    /** GET /reports/{report_id} */
    public function getReport(string $reportId): array
    {
        return $this->get("/reports/{$reportId}");
    }

    /** GET /reports/{report_id}/kpis */
    public function getReportKpis(string $reportId): array
    {
        return $this->get("/reports/{$reportId}/kpis");
    }

    /** GET /report-formats */
    public function getReportFormats(): array
    {
        return $this->get('/report-formats');
    }

    /**
     * GET /reports/{report_id}/download
     * Retorna la URL de descarga directa para usar en FileResponse o redirect.
     */
    public function getReportDownloadUrl(string $reportId, string $format = 'pdf'): string
    {
        return "{$this->baseUrl}/reports/{$reportId}/download?format={$format}";
    }

    /**
     * Descarga el contenido binario de un reporte.
     */
    public function downloadReport(string $reportId, string $format = 'pdf'): \Illuminate\Http\Client\Response
    {
        return Http::timeout(120)
            ->get("{$this->baseUrl}/reports/{$reportId}/download", ['format' => $format]);
    }

    // -------------------------------------------------------------------------
    // CSV
    // -------------------------------------------------------------------------

    /** GET /csv/reports */
    public function getCsvReports(int $limit = 50): array
    {
        return $this->get('/csv/reports', ['limit' => $limit]);
    }

    /** GET /csv/metrics/{report_id} */
    public function getCsvMetrics(string $reportId): array
    {
        return $this->get("/csv/metrics/{$reportId}");
    }

    /** GET /csv/report/{report_id}/rows */
    public function getCsvRows(string $reportId): array
    {
        return $this->get("/csv/report/{$reportId}/rows");
    }

    /** DELETE /csv/report/{report_id} */
    public function deleteCsvReport(string $reportId): array
    {
        return $this->delete("/csv/report/{$reportId}");
    }

    /**
     * POST /csv/upload  (multipart file)
     */
    public function uploadCsv(string $filePath, string $filename): array
    {
        $response = Http::timeout(120)
            ->attach('file', file_get_contents($filePath), $filename)
            ->post("{$this->baseUrl}/csv/upload");

        return $response->json() ?? [];
    }

    /** URL del reporte HTML de análisis CSV */
    public function getCsvReportHtmlUrl(string $reportId): string
    {
        return "{$this->baseUrl}/csv/report/{$reportId}";
    }

    /** URL de descarga Excel del análisis CSV */
    public function getCsvExcelUrl(string $reportId): string
    {
        return "{$this->baseUrl}/csv/excel/{$reportId}";
    }

    /** URL de descarga PDF profesional del análisis CSV */
    public function getCsvPdfUrl(string $reportId): string
    {
        return "{$this->baseUrl}/csv/pdf/{$reportId}";
    }

    // -------------------------------------------------------------------------
    // Scheduler
    // -------------------------------------------------------------------------

    public function getSchedulerJobs(): array
    {
        return $this->get('/scheduler/jobs');
    }

    public function createSchedulerJob(array $data): array
    {
        return $this->post('/scheduler/jobs', $data);
    }

    public function deleteSchedulerJob(string $jobId): array
    {
        return $this->delete("/scheduler/jobs/{$jobId}");
    }

    public function pauseSchedulerJob(string $jobId): array
    {
        return $this->post("/scheduler/jobs/{$jobId}/pause");
    }

    public function resumeSchedulerJob(string $jobId): array
    {
        return $this->post("/scheduler/jobs/{$jobId}/resume");
    }

    public function runNowSchedulerJob(string $jobId): array
    {
        return $this->post("/scheduler/jobs/{$jobId}/run-now");
    }

    // -------------------------------------------------------------------------
    // Alertas
    // -------------------------------------------------------------------------

    public function getAlertRules(): array
    {
        return $this->get('/alerts/rules');
    }

    public function createAlertRule(array $data): array
    {
        return $this->post('/alerts/rules', $data);
    }

    public function updateAlertRule(string $ruleId, array $data): array
    {
        $response = Http::timeout($this->timeout)
            ->put("{$this->baseUrl}/alerts/rules/{$ruleId}", $data);
        $this->logIfError($response, "/alerts/rules/{$ruleId}");
        return $response->json() ?? [];
    }

    public function deleteAlertRule(string $ruleId): array
    {
        return $this->delete("/alerts/rules/{$ruleId}");
    }

    public function toggleAlertRule(string $ruleId): array
    {
        return $this->post("/alerts/rules/{$ruleId}/toggle");
    }

    public function getAlertHistory(int $limit = 50): array
    {
        return $this->get('/alerts/history', ['limit' => $limit]);
    }

    public function testWebhook(array $data): array
    {
        return $this->post('/alerts/test-webhook', $data);
    }

    public function checkAlerts(string $reportId): array
    {
        return $this->post("/alerts/check/{$reportId}");
    }

    // -------------------------------------------------------------------------
    // Tendencias
    // -------------------------------------------------------------------------

    public function getGlobalTrends(): array
    {
        return $this->get('/trends');
    }

    public function getHostTrends(): array
    {
        return $this->get('/trends/hosts');
    }

    public function getTaskTrends(string $taskId): array
    {
        return $this->get("/trends/{$taskId}");
    }

    public function backfillTrends(): array
    {
        return $this->post('/trends/backfill');
    }

    /** POST /trends/backfill-csv — rellena desde reportes CSV procesados */
    public function backfillTrendsCsv(): array
    {
        return $this->post('/trends/backfill-csv');
    }

    // -------------------------------------------------------------------------
    // Diff de Reportes
    // -------------------------------------------------------------------------

    public function getReportDiff(string $idA, string $idB): array
    {
        return $this->get("/reports/{$idA}/diff/{$idB}");
    }

    public function getReportDiffPdfUrl(string $idA, string $idB): string
    {
        return "{$this->baseUrl}/reports/{$idA}/diff/{$idB}/pdf";
    }

    public function getReportDiffExcelUrl(string $idA, string $idB): string
    {
        return "{$this->baseUrl}/reports/{$idA}/diff/{$idB}/excel";
    }
}
