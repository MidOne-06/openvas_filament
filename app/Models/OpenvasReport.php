<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenvasReport extends Model
{
    protected $table = 'openvas_reports';

    protected $fillable = [
        'gvm_id', 'task_id', 'task_name', 'scan_start', 'scan_end',
        'total_vulns', 'critical', 'high', 'medium', 'low', 'info_count',
        'kpis', 'last_synced_at',
    ];

    protected $casts = [
        'kpis'           => 'array',
        'last_synced_at' => 'datetime',
        'total_vulns'    => 'integer',
        'critical'       => 'integer',
        'high'           => 'integer',
        'medium'         => 'integer',
        'low'            => 'integer',
        'info_count'     => 'integer',
    ];

    /**
     * Sincroniza desde la respuesta de GET /reports/{id} (details).
     * El backend retorna los conteos a nivel raíz:
     *   total_vulns, critical, high, medium, low, info
     */
    public static function syncFromApi(array $apiData): self
    {
        return self::updateOrCreate(
            ['gvm_id' => $apiData['id']],
            [
                'task_id'        => $apiData['task_id'] ?? null,
                'task_name'      => $apiData['task_name'] ?? null,
                'scan_start'     => $apiData['creation_time'] ?? $apiData['scan_start'] ?? null,
                'scan_end'       => $apiData['scan_end'] ?? null,
                // Los conteos vienen a nivel raíz en el backend (no en result_count)
                'total_vulns'    => (int) ($apiData['total_vulns'] ?? 0),
                'critical'       => (int) ($apiData['critical'] ?? 0),
                'high'           => (int) ($apiData['high'] ?? 0),
                'medium'         => (int) ($apiData['medium'] ?? 0),
                'low'            => (int) ($apiData['low'] ?? 0),
                'info_count'     => (int) ($apiData['info'] ?? $apiData['info_count'] ?? 0),
                'last_synced_at' => now(),
            ]
        );
    }
}
