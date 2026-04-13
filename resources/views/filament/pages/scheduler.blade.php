<x-filament-panels::page>
<style>
:root {
    --sc-bg:#ffffff;--sc-border:#e5e7eb;--sc-text:#111827;--sc-muted:#6b7280;
    --sc-card:#f9fafb;--sc-row:#f3f4f6;--sc-input-bg:#ffffff;--sc-input-border:#d1d5db;
}
.dark {
    --sc-bg:#1f2937;--sc-border:#374151;--sc-text:#f9fafb;--sc-muted:#9ca3af;
    --sc-card:#111827;--sc-row:#1f2937;--sc-input-bg:#1f2937;--sc-input-border:#4b5563;
}
.sc-card  { background:var(--sc-bg);border:1px solid var(--sc-border);border-radius:12px;padding:20px;margin-bottom:16px; }
.sc-input { width:100%;background:var(--sc-input-bg);border:1px solid var(--sc-input-border);border-radius:8px;padding:8px 12px;color:var(--sc-text);font-size:13px;box-sizing:border-box; }
.sc-label { font-size:12px;font-weight:600;color:var(--sc-muted);margin-bottom:4px;display:block; }
.sc-btn   { border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer; }
.sc-badge { display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700; }
.sc-hint  { font-size:11px;color:var(--sc-muted);margin-top:4px; }
select.sc-input { appearance:auto;-webkit-appearance:auto;background-image:none; }
</style>

{{-- ── Formulario de creacion ── --}}
@if($showForm)
<div class="sc-card" style="border-color:#6366f1;">
    <p style="font-size:14px;font-weight:700;color:var(--sc-text);margin:0 0 16px;">Nueva Tarea Programada</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

        {{-- Nombre y Task ID --}}
        <div>
            <label class="sc-label">Nombre de la Tarea *</label>
            <input class="sc-input" wire:model="form_name" placeholder="Ej: Escaneo nocturno de produccion">
        </div>
        <div>
            <label class="sc-label">ID de Tarea OpenVAS (opcional)</label>
            <input class="sc-input" wire:model="form_task_id" placeholder="UUID de tarea existente en OpenVAS">
        </div>

        {{-- Hosts y Scan Config --}}
        <div>
            <label class="sc-label">Hosts / Redes *</label>
            <input class="sc-input" wire:model="form_hosts" placeholder="192.168.1.0/24, 10.0.0.1">
            <p class="sc-hint">Separa multiples destinos con coma</p>
        </div>
        <div>
            <label class="sc-label">Configuracion de Escaneo *</label>
            @if(count($scanConfigs) > 0)
                <select class="sc-input" wire:model="form_scan_config_id">
                    <option value="">-- Selecciona una configuracion --</option>
                    @foreach($scanConfigs as $cfg)
                        <option value="{{ $cfg['id'] }}">{{ $cfg['name'] }}</option>
                    @endforeach
                </select>
                <p class="sc-hint">{{ count($scanConfigs) }} configuraciones disponibles</p>
            @else
                <input class="sc-input" wire:model="form_scan_config_id"
                       placeholder="UUID de la config OpenVAS (backend no conectado)">
                <p class="sc-hint" style="color:#ef4444;">
                    Backend no disponible — ingresa el UUID manualmente o arranca el backend primero
                </p>
            @endif
        </div>

        {{-- Port List --}}
        <div style="grid-column:span 2;">
            <label class="sc-label">Lista de Puertos (opcional)</label>
            @if(count($portLists) > 0)
                <select class="sc-input" wire:model="form_port_list_id">
                    <option value="">-- Usar lista de puertos por defecto --</option>
                    @foreach($portLists as $pl)
                        <option value="{{ $pl['id'] }}">{{ $pl['name'] }}</option>
                    @endforeach
                </select>
                <p class="sc-hint">{{ count($portLists) }} listas disponibles — si no seleccionas ninguna se usa la lista por defecto de OpenVAS</p>
            @else
                <input class="sc-input" wire:model="form_port_list_id"
                       placeholder="UUID de lista de puertos (opcional) — backend no conectado">
                <p class="sc-hint">Deja vacio para usar la lista por defecto</p>
            @endif
        </div>

        {{-- Tipo de programacion --}}
        <div>
            <label class="sc-label">Tipo de Programacion</label>
            <select class="sc-input" wire:model.live="form_schedule_type">
                <option value="daily">Diario (a una hora fija)</option>
                <option value="weekly">Semanal (dia + hora)</option>
                <option value="monthly">Mensual (dia del mes + hora)</option>
                <option value="interval">Intervalo repetitivo (cada N horas)</option>
                <option value="cron">Expresion Cron personalizada</option>
                <option value="once">Una sola vez (fecha y hora exacta)</option>
            </select>
        </div>

        {{-- Campos segun tipo --}}
        @if(in_array($form_schedule_type, ['daily', 'weekly', 'monthly', 'interval']))
        <div>
            <label class="sc-label">
                @if($form_schedule_type === 'interval') Cada cuantas Horas @else Hora (0-23) @endif
            </label>
            <input class="sc-input" type="number" min="0" max="23" wire:model="form_hour">
        </div>
        @endif

        @if(in_array($form_schedule_type, ['daily', 'weekly', 'monthly']))
        <div>
            <label class="sc-label">Minuto (0-59)</label>
            <input class="sc-input" type="number" min="0" max="59" wire:model="form_minute">
        </div>
        @endif

        @if($form_schedule_type === 'interval')
        <div>
            <label class="sc-label">Minutos adicionales (0-59)</label>
            <input class="sc-input" type="number" min="0" max="59" wire:model="form_minute">
            <p class="sc-hint">Ej: Horas=2, Minutos=30 → se ejecuta cada 2h 30min</p>
        </div>
        @endif

        @if($form_schedule_type === 'weekly')
        <div>
            <label class="sc-label">Dia de la Semana</label>
            <select class="sc-input" wire:model="form_day_of_week">
                <option value="mon">Lunes</option>
                <option value="tue">Martes</option>
                <option value="wed">Miercoles</option>
                <option value="thu">Jueves</option>
                <option value="fri">Viernes</option>
                <option value="sat">Sabado</option>
                <option value="sun">Domingo</option>
            </select>
        </div>
        @endif

        @if($form_schedule_type === 'monthly')
        <div>
            <label class="sc-label">Dia del Mes (1-31)</label>
            <input class="sc-input" type="number" min="1" max="31" wire:model="form_day_of_month">
        </div>
        @endif

        @if($form_schedule_type === 'cron')
        <div style="grid-column:span 2;">
            <label class="sc-label">Expresion Cron</label>
            <input class="sc-input" wire:model="form_cron" placeholder="0 2 * * *">
            <p class="sc-hint">Formato: minuto hora dia-mes mes dia-semana &nbsp;|&nbsp; Ejemplo: <code>0 2 * * *</code> = todos los dias a las 02:00 &nbsp;|&nbsp; <code>0 9 * * 1</code> = cada lunes a las 09:00</p>
        </div>
        @endif

        @if($form_schedule_type === 'once')
        <div style="grid-column:span 2;">
            <label class="sc-label">Fecha y Hora de Ejecucion</label>
            <input class="sc-input" type="datetime-local" wire:model="form_run_date">
            <p class="sc-hint">El escaneo se ejecutara una unica vez en la fecha indicada</p>
        </div>
        @endif

        {{-- Webhook --}}
        <div style="grid-column:span 2;">
            <label class="sc-label">Webhook de Notificacion (opcional)</label>
            <input class="sc-input" wire:model="form_notify_webhook" placeholder="https://hooks.slack.com/... — se notifica al completar el escaneo">
        </div>

    </div>

    <div style="display:flex;gap:10px;margin-top:16px;align-items:center;flex-wrap:wrap;">
        <button class="sc-btn" style="background:#6366f1;color:#fff;" wire:click="createJob">
            Crear Tarea
        </button>
        <button class="sc-btn" style="background:var(--sc-row);color:var(--sc-text);" wire:click="toggleForm">
            Cancelar
        </button>
        @if(count($scanConfigs) === 0)
        <button class="sc-btn" style="background:#0891b2;color:#fff;font-size:11px;"
                wire:click="loadOpenVasOptions"
                title="Recarga las configuraciones desde el backend OpenVAS">
            &#8635; Recargar opciones de OpenVAS
        </button>
        @endif
    </div>
</div>
@endif

{{-- ── Lista de Tareas ── --}}
@if(count($jobs) === 0)
<div class="sc-card" style="text-align:center;padding:48px 20px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.2" style="margin:0 auto 12px;display:block;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p style="color:var(--sc-muted);font-size:13px;margin:0;">No hay tareas programadas. Crea una con el boton <strong>+ Nueva Tarea</strong>.</p>
</div>
@else
<div class="sc-card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:var(--sc-row);">
                <th style="text-align:left;padding:10px 16px;font-size:11px;font-weight:700;color:var(--sc-muted);text-transform:uppercase;letter-spacing:0.05em;">Nombre</th>
                <th style="text-align:left;padding:10px 16px;font-size:11px;font-weight:700;color:var(--sc-muted);text-transform:uppercase;">Hosts Objetivo</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--sc-muted);text-transform:uppercase;">Frecuencia</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--sc-muted);text-transform:uppercase;">Proximo Escaneo</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--sc-muted);text-transform:uppercase;">Ult. Estado</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--sc-muted);text-transform:uppercase;">Estado</th>
                <th style="text-align:right;padding:10px 16px;font-size:11px;font-weight:700;color:var(--sc-muted);text-transform:uppercase;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        @foreach($jobs as $job)
            @php
                $jobId       = $job['job_id'] ?? $job['id'] ?? '';
                $enabled     = (bool)($job['enabled'] ?? 1);
                $jobTaskName = addslashes($job['task_name'] ?? '');
                $status  = $enabled ? 'activo' : 'pausado';

                $badgeColor = $enabled
                    ? ['bg'=>'#d1fae5','txt'=>'#065f46']
                    : ['bg'=>'#fef3c7','txt'=>'#92400e'];

                $nextRun = $job['next_run_time'] ?? $job['next_run'] ?? null;

                $hosts = is_array($job['target_hosts'] ?? null)
                    ? implode(', ', $job['target_hosts'])
                    : ($job['target_hosts'] ?? $job['hosts'] ?? '-');

                // Traduccion de schedule_type
                $tipoMap = [
                    'daily'    => 'Diario',
                    'weekly'   => 'Semanal',
                    'monthly'  => 'Mensual',
                    'interval' => 'Intervalo',
                    'intervalo'=> 'Intervalo',
                    'cron'     => 'Cron',
                    'once'     => 'Una vez',
                ];
                $tipo = $tipoMap[$job['schedule_type'] ?? ''] ?? ucfirst($job['schedule_type'] ?? '-');

                // Traduccion de last_status
                $statusMap = [
                    'completado'  => ['txt'=>'Completado',   'color'=>'#16a34a'],
                    'completed'   => ['txt'=>'Completado',   'color'=>'#16a34a'],
                    'en_curso'    => ['txt'=>'En curso',     'color'=>'#2563eb'],
                    'running'     => ['txt'=>'En curso',     'color'=>'#2563eb'],
                    'iniciando'   => ['txt'=>'Iniciando...',  'color'=>'#0891b2'],
                    'detenido'    => ['txt'=>'Detenido',     'color'=>'#d97706'],
                    'timeout'     => ['txt'=>'Tiempo agotado','color'=>'#d97706'],
                    'error'       => ['txt'=>'Error de inicio','color'=>'#dc2626'],
                    'pending'     => ['txt'=>'Pendiente',    'color'=>'#9ca3af'],
                ];
                $lastStatus = $job['last_status'] ?? null;
                $lastStatusInfo = $statusMap[$lastStatus] ?? ['txt'=> ($lastStatus ? ucfirst($lastStatus) : '-'), 'color'=>'#9ca3af'];
            @endphp
            <tr style="border-top:1px solid var(--sc-border);">
                {{-- Nombre --}}
                <td style="padding:12px 16px;">
                    <p style="font-size:13px;font-weight:600;color:var(--sc-text);margin:0;">{{ $job['task_name'] ?? '-' }}</p>
                    @if(!empty($jobId))
                    <p style="font-size:10px;color:var(--sc-muted);margin:2px 0 0;font-family:monospace;">{{ Str::limit($jobId, 20) }}</p>
                    @endif
                    @if(!empty($job['run_count']))
                    <p style="font-size:10px;color:var(--sc-muted);margin:2px 0 0;">Ejecuciones: {{ $job['run_count'] }}</p>
                    @endif
                </td>

                {{-- Hosts --}}
                <td style="padding:12px 16px;font-size:12px;color:var(--sc-muted);font-family:monospace;">
                    {{ Str::limit($hosts, 35) }}
                </td>

                {{-- Frecuencia --}}
                <td style="padding:12px 16px;text-align:center;">
                    <span class="sc-badge" style="background:#e0e7ff;color:#3730a3;">{{ $tipo }}</span>
                    @if($tipo === 'Diario' && isset($job['hour']))
                    <p style="font-size:10px;color:var(--sc-muted);margin:3px 0 0;">
                        {{ str_pad($job['hour'],2,'0',STR_PAD_LEFT) }}:{{ str_pad($job['minute'] ?? 0,2,'0',STR_PAD_LEFT) }} UTC
                    </p>
                    @elseif($tipo === 'Intervalo' && isset($job['hour']))
                    <p style="font-size:10px;color:var(--sc-muted);margin:3px 0 0;">
                        Cada {{ $job['hour'] }}h {{ $job['minute'] ?? 0 }}min
                    </p>
                    @elseif(!empty($job['cron_expression']))
                    <p style="font-size:10px;color:var(--sc-muted);margin:3px 0 0;font-family:monospace;">{{ $job['cron_expression'] }}</p>
                    @endif
                </td>

                {{-- Proximo escaneo --}}
                <td style="padding:12px 16px;text-align:center;font-size:12px;color:var(--sc-muted);">
                    @if($nextRun)
                        {{ \Carbon\Carbon::parse($nextRun)->format('d/m/Y') }}<br>
                        <span style="font-size:11px;">{{ \Carbon\Carbon::parse($nextRun)->format('H:i') }} UTC</span>
                    @else
                        <span style="color:#9ca3af;">—</span>
                    @endif
                </td>

                {{-- Ultimo estado --}}
                <td style="padding:12px 16px;text-align:center;">
                    <span style="font-size:12px;font-weight:600;color:{{ $lastStatusInfo['color'] }};">
                        {{ $lastStatusInfo['txt'] }}
                    </span>
                    @if(!empty($job['last_run']))
                    <p style="font-size:10px;color:var(--sc-muted);margin:2px 0 0;">
                        {{ \Carbon\Carbon::parse($job['last_run'])->format('d/m/Y H:i') }}
                    </p>
                    @endif
                </td>

                {{-- Activo/Pausado --}}
                <td style="padding:12px 16px;text-align:center;">
                    <span class="sc-badge" style="background:{{ $badgeColor['bg'] }};color:{{ $badgeColor['txt'] }};">
                        {{ strtoupper($status) }}
                    </span>
                </td>

                {{-- Acciones --}}
                <td style="padding:12px 16px;text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                        <button class="sc-btn" style="background:#059669;color:#fff;font-size:11px;"
                            wire:click="runNow('{{ $jobId }}')"
                            title="Ejecutar escaneo ahora mismo, independientemente del horario">
                            &#9654; Ejecutar Ahora
                        </button>
                        @if(!$enabled)
                        <button class="sc-btn" style="background:#2563eb;color:#fff;font-size:11px;"
                            wire:click="resumeJob('{{ $jobId }}')"
                            title="Reanudar la programacion de esta tarea">
                            Reanudar
                        </button>
                        @else
                        <button class="sc-btn" style="background:#d97706;color:#fff;font-size:11px;"
                            wire:click="pauseJob('{{ $jobId }}')"
                            title="Pausar sin eliminar — se puede reanudar luego">
                            Pausar
                        </button>
                        @endif
                        <button class="sc-btn" style="background:#dc2626;color:#fff;font-size:11px;"
                            wire:click="deleteJob('{{ $jobId }}')"
                            onclick="return confirm('Eliminar la tarea: {{ $jobTaskName }}?')">
                            Eliminar
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p style="font-size:11px;color:var(--sc-muted);margin:8px 0 0;">{{ count($jobs) }} tarea(s) programada(s)</p>
@endif

</x-filament-panels::page>
