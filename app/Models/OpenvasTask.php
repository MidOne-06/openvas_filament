<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class OpenvasTask extends Model
{
    protected $table = 'openvas_tasks';

    protected $fillable = [
        'gvm_id', 'name', 'status', 'progress',
        'config_id', 'target_id', 'last_report_id',
        'kpis', 'last_synced_at',
    ];

    protected $casts = [
        'kpis'           => 'array',
        'last_synced_at' => 'datetime',
        'progress'       => 'integer',
    ];

    /** Color del badge según el estado */
    public function statusColor(): string
    {
        return config('openvas.task_status_colors.' . $this->status, 'gray');
    }

    /** Icono según el estado */
    public function statusIcon(): string
    {
        return match ($this->status) {
            'Done'        => 'heroicon-o-check-circle',
            'Running'     => 'heroicon-o-play-circle',
            'Requested'   => 'heroicon-o-clock',
            'Stopped'     => 'heroicon-o-pause-circle',
            'Interrupted' => 'heroicon-o-exclamation-circle',
            default       => 'heroicon-o-question-mark-circle',
        };
    }

    /** Sincroniza este registro con datos del backend */
    public static function syncFromApi(array $apiData): self
    {
        return self::updateOrCreate(
            ['gvm_id' => $apiData['id']],
            [
                'name'           => $apiData['name'] ?? '',
                'status'         => $apiData['status'] ?? 'New',
                'progress'       => (int) ($apiData['progress'] ?? 0),
                'last_report_id' => $apiData['last_report_id'] ?? null,
                'last_synced_at' => now(),
            ]
        );
    }
}
