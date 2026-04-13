<x-filament-panels::page>
<style>
:root {
    --al-bg:#ffffff;--al-border:#e5e7eb;--al-text:#111827;
    --al-muted:#6b7280;--al-row:#f9fafb;--al-card:#ffffff;
    --al-input-bg:#ffffff;--al-input-border:#d1d5db;
}
.dark {
    --al-bg:#1f2937;--al-border:#374151;--al-text:#f9fafb;
    --al-muted:#9ca3af;--al-row:#111827;--al-card:#1f2937;
    --al-input-bg:#1f2937;--al-input-border:#4b5563;
}
.al-card  { background:var(--al-bg);border:1px solid var(--al-border);border-radius:12px;margin-bottom:16px; }
.al-input { width:100%;background:var(--al-input-bg);border:1px solid var(--al-input-border);border-radius:8px;padding:8px 12px;color:var(--al-text);font-size:13px;box-sizing:border-box; }
.al-label { font-size:12px;font-weight:600;color:var(--al-muted);margin-bottom:4px;display:block; }
.al-hint  { font-size:11px;color:var(--al-muted);margin-top:3px; }
.al-btn   { border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer; }
.al-tab   { padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;border:none;background:transparent;border-bottom:2px solid transparent;color:var(--al-muted); }
.al-tab.active { color:#6366f1;border-bottom-color:#6366f1; }
.al-section { font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;letter-spacing:0.08em;padding:12px 0 6px;border-bottom:1px solid var(--al-border);margin-bottom:12px;grid-column:span 2; }
select.al-input { appearance:auto;-webkit-appearance:auto;background-image:none; }
</style>

{{-- Tabs --}}
<div style="display:flex;gap:0;border-bottom:1px solid var(--al-border);margin-bottom:20px;">
    <button class="al-tab {{ $activeTab === 'rules' ? 'active' : '' }}" wire:click="setTab('rules')">
        Reglas de Alerta ({{ count($rules) }})
    </button>
    <button class="al-tab {{ $activeTab === 'history' ? 'active' : '' }}" wire:click="setTab('history')">
        Historial de Disparos ({{ count($history) }})
    </button>
</div>

{{-- ── Formulario: Probar Webhook ── --}}
@if($showTestForm)
<div class="al-card" style="padding:20px;border-color:#0ea5e9;margin-bottom:16px;">
    <p style="font-size:14px;font-weight:700;color:var(--al-text);margin:0 0 6px;">Probar Webhook</p>
    <p class="al-hint" style="margin:0 0 14px;">Envia un payload JSON de prueba a la URL indicada para verificar que la recibe correctamente.</p>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;align-items:end;">
        <div>
            <label class="al-label">URL del Webhook *</label>
            <input class="al-input" wire:model="test_webhook_url" placeholder="https://hooks.slack.com/services/... o https://webhook.site/tu-id">
            <p class="al-hint">Para pruebas rapidas usa <strong>https://webhook.site</strong> (gratuito, genera una URL temporal)</p>
        </div>
        <div>
            <label class="al-label">Mensaje de prueba</label>
            <input class="al-input" wire:model="test_message" placeholder="Mensaje de prueba">
        </div>
    </div>
    <div style="display:flex;gap:10px;margin-top:14px;">
        <button class="al-btn" style="background:#0ea5e9;color:#fff;" wire:click="sendTestWebhook">Enviar Prueba</button>
        <button class="al-btn" style="background:var(--al-row);color:var(--al-text);" wire:click="$set('showTestForm', false)">Cancelar</button>
    </div>
</div>
@endif

{{-- ══════════════ TAB: Reglas ══════════════ --}}
@if($activeTab === 'rules')

{{-- ── Formulario: Nueva Regla ── --}}
@if($showForm)
<div class="al-card" style="padding:20px;border-color:#6366f1;">
    <p style="font-size:15px;font-weight:700;color:var(--al-text);margin:0 0 4px;">Nueva Regla de Alerta</p>
    <p class="al-hint" style="margin:0 0 16px;">
        La regla se dispara automaticamente cada vez que un reporte procesado cumple las condiciones.
        Debe configurar al menos una condicion (CVSS, Severidad o Minimo de vulns).
    </p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

        {{-- SECCION: Identificacion --}}
        <div class="al-section">Identificacion de la Regla</div>

        <div>
            <label class="al-label">Nombre de la Regla *</label>
            <input class="al-input" wire:model="form_name" placeholder="Ej: Alerta Criticos en Produccion">
            <p class="al-hint">Nombre descriptivo para identificar la regla en el historial</p>
        </div>
        <div>
            <label class="al-label">Descripcion (opcional)</label>
            <input class="al-input" wire:model="form_description" placeholder="Ej: Notifica cuando hay CVSS mayor a 9 en servidores criticos">
        </div>

        {{-- SECCION: Condiciones --}}
        <div class="al-section">Condiciones de Disparo (al menos una)</div>

        <div>
            <label class="al-label">Umbral CVSS minimo</label>
            <input class="al-input" type="number" step="0.1" min="0" max="10" wire:model="form_cvss_threshold">
            <p class="al-hint">Se dispara si existe al menos 1 vulnerabilidad con CVSS &ge; este valor. Rango: 0.0 &ndash; 10.0</p>
        </div>
        <div>
            <label class="al-label">Nivel de Severidad (opcional)</label>
            <select class="al-input" wire:model="form_severity_level">
                <option value="">Cualquier severidad</option>
                <option value="CRITICAL">CRITICO — CVSS 9.0–10.0</option>
                <option value="HIGH">ALTO — CVSS 7.0–8.9</option>
                <option value="MEDIUM">MEDIO — CVSS 4.0–6.9</option>
                <option value="LOW">BAJO — CVSS 0.1–3.9</option>
            </select>
            <p class="al-hint">Si se combina con CVSS, ambas condiciones se evaluan por separado (OR)</p>
        </div>
        <div>
            <label class="al-label">Minimo de Vulnerabilidades Totales (opcional)</label>
            <input class="al-input" type="number" min="1" wire:model="form_new_vuln_count" placeholder="Ej: 50">
            <p class="al-hint">Se dispara si el reporte tiene este numero o mas de vulnerabilidades en total</p>
        </div>
        <div>
            <label class="al-label">Task IDs a vigilar (opcional, separados por coma)</label>
            <input class="al-input" wire:model="form_task_ids" placeholder="Dejar vacio = aplica a TODOS los reportes">
            <p class="al-hint">Si se especifican UUIDs, la regla solo aplica a esas tareas OpenVAS</p>
        </div>

        {{-- SECCION: Notificaciones --}}
        <div class="al-section">Canales de Notificacion (al menos uno recomendado)</div>

        <div>
            <label class="al-label">Webhook URL (Slack / Teams / Generico)</label>
            <input class="al-input" wire:model="form_webhook_url" placeholder="https://hooks.slack.com/services/... o https://webhook.site/...">
        </div>
        <div>
            <label class="al-label">Tipo de Webhook</label>
            <select class="al-input" wire:model="form_webhook_type">
                <option value="generic">Generico (JSON estandar)</option>
                <option value="slack">Slack (formato con bloques)</option>
                <option value="teams">Microsoft Teams (adaptive card)</option>
            </select>
            <p class="al-hint">Determina el formato del payload enviado al webhook</p>
        </div>
        <div style="grid-column:span 2;">
            <label class="al-label">Email de Notificacion (opcional)</label>
            <input class="al-input" type="email" wire:model="form_email_to" placeholder="seguridad@tuempresa.com">
            <p class="al-hint">Requiere configurar SMTP en el archivo .env del backend (SMTP_HOST, SMTP_USER, SMTP_PASS)</p>
        </div>

    </div>

    <div style="display:flex;gap:10px;margin-top:16px;">
        <button class="al-btn" style="background:#6366f1;color:#fff;" wire:click="createRule">Crear Regla</button>
        <button class="al-btn" style="background:var(--al-row);color:var(--al-text);" wire:click="toggleForm">Cancelar</button>
    </div>
</div>
@endif

{{-- Lista de Reglas --}}
@if(count($rules) === 0)
<div class="al-card" style="text-align:center;padding:48px 20px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.2" style="margin:0 auto 12px;display:block;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
    </svg>
    <p style="color:var(--al-muted);font-size:13px;margin:0 0 4px;">No hay reglas de alerta configuradas.</p>
    <p style="color:var(--al-muted);font-size:12px;margin:0;">Haz clic en <strong>+ Nueva Regla</strong> para crear la primera.</p>
</div>
@else
<div class="al-card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:var(--al-row);">
                <th style="text-align:left;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Nombre / Descripcion</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">CVSS Min</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Severidad</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Min. Vulns</th>
                <th style="text-align:left;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Notificacion</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Estado</th>
                <th style="text-align:right;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rules as $rule)
            @php
                $ruleId   = (string)($rule['rule_id'] ?? $rule['id'] ?? '');
                $enabled  = (bool)($rule['enabled'] ?? true);
                $ruleName = addslashes($rule['name'] ?? '');

                $notif = collect([
                    !empty($rule['webhook_url']) ? ('Webhook (' . ucfirst($rule['webhook_type'] ?? 'gen') . ')') : null,
                    !empty($rule['email_to'])    ? 'Email'   : null,
                ])->filter()->implode(' + ') ?: 'Sin notificacion';

                $sevMap  = ['CRITICAL'=>'CRITICO','HIGH'=>'ALTO','MEDIUM'=>'MEDIO','LOW'=>'BAJO'];
                $sevText = $sevMap[$rule['severity_level'] ?? ''] ?? ($rule['severity_level'] ?: 'Cualquiera');
            @endphp
            <tr style="border-top:1px solid var(--al-border);">
                <td style="padding:12px 16px;">
                    <p style="font-size:13px;font-weight:600;color:var(--al-text);margin:0;">{{ $rule['name'] ?? '-' }}</p>
                    @if(!empty($rule['description']))
                    <p style="font-size:11px;color:var(--al-muted);margin:2px 0 0;">{{ $rule['description'] }}</p>
                    @endif
                    @if(!empty($rule['trigger_count']))
                    <p style="font-size:10px;color:#6366f1;margin:2px 0 0;font-weight:600;">{{ $rule['trigger_count'] }} disparo(s)</p>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($rule['cvss_threshold'] ?? null)
                    <span style="font-size:14px;font-weight:700;color:{{ ($rule['cvss_threshold'] >= 9) ? '#dc2626' : (($rule['cvss_threshold'] >= 7) ? '#d97706' : '#ca8a04') }};">
                        &ge; {{ number_format($rule['cvss_threshold'], 1) }}
                    </span>
                    @else
                    <span style="color:var(--al-muted);">—</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    @php
                        $sevColors = ['CRITICAL'=>['bg'=>'#fee2e2','txt'=>'#991b1b'],'HIGH'=>['bg'=>'#ffedd5','txt'=>'#9a3412'],'MEDIUM'=>['bg'=>'#fef9c3','txt'=>'#854d0e'],'LOW'=>['bg'=>'#f0fdf4','txt'=>'#166534']];
                        $sc = $sevColors[$rule['severity_level'] ?? ''] ?? null;
                    @endphp
                    @if($sc)
                    <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;background:{{ $sc['bg'] }};color:{{ $sc['txt'] }};">{{ $sevText }}</span>
                    @else
                    <span style="color:var(--al-muted);font-size:12px;">Cualquiera</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:13px;font-weight:600;color:var(--al-muted);">
                    {{ $rule['new_vuln_count'] ? '>= '.$rule['new_vuln_count'] : '—' }}
                </td>
                <td style="padding:12px 16px;font-size:11px;color:var(--al-muted);">{{ $notif }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    <span style="display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;
                        background:{{ $enabled ? '#d1fae5' : '#f3f4f6' }};color:{{ $enabled ? '#065f46' : '#6b7280' }};">
                        {{ $enabled ? 'ACTIVA' : 'INACTIVA' }}
                    </span>
                </td>
                <td style="padding:12px 16px;text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                        <button class="al-btn" style="background:{{ $enabled ? '#6b7280' : '#059669' }};color:#fff;font-size:11px;"
                            wire:click="toggleRule('{{ $ruleId }}')"
                            title="{{ $enabled ? 'Desactivar regla temporalmente' : 'Activar regla' }}">
                            {{ $enabled ? 'Desactivar' : 'Activar' }}
                        </button>
                        <button class="al-btn" style="background:#dc2626;color:#fff;font-size:11px;"
                            wire:click="deleteRule('{{ $ruleId }}')"
                            onclick="return confirm('Eliminar la regla: {{ $ruleName }}? Esta accion no se puede deshacer.')">
                            Eliminar
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p style="font-size:11px;color:var(--al-muted);margin:8px 0 0;">{{ count($rules) }} regla(s) configurada(s)</p>
@endif

{{-- ══════════════ TAB: Historial ══════════════ --}}
@elseif($activeTab === 'history')

@if(count($history) === 0)
<div class="al-card" style="text-align:center;padding:48px 20px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.2" style="margin:0 auto 12px;display:block;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p style="color:var(--al-muted);font-size:13px;margin:0 0 4px;">No hay alertas disparadas aun.</p>
    <p style="color:var(--al-muted);font-size:12px;margin:0;">Las alertas aparecen aqui cuando un reporte cumple las condiciones de alguna regla activa.</p>
</div>
@else
<div class="al-card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:var(--al-row);">
                <th style="text-align:left;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Regla Disparada</th>
                <th style="text-align:left;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Razon / Condicion</th>
                <th style="text-align:left;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Tarea / Reporte</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Webhook</th>
                <th style="text-align:center;padding:10px 16px;font-size:11px;font-weight:700;color:var(--al-muted);text-transform:uppercase;">Fecha</th>
            </tr>
        </thead>
        <tbody>
        @foreach($history as $h)
            @php
                $wok = $h['webhook_status'] ?? '';
                $wokLabel = match(true) {
                    $wok == 200 || $wok === 'ok' => 'Enviado',
                    $wok > 0 => 'HTTP '.$wok,
                    default => ($wok ? 'Error' : 'Sin webhook'),
                };
                $wokBg  = ($wok == 200 || $wok === 'ok') ? '#d1fae5' : ($wok ? '#fef2f2' : '#f3f4f6');
                $wokTxt = ($wok == 200 || $wok === 'ok') ? '#065f46' : ($wok ? '#991b1b' : '#6b7280');
            @endphp
            <tr style="border-top:1px solid var(--al-border);">
                <td style="padding:11px 16px;">
                    <p style="font-size:13px;font-weight:600;color:var(--al-text);margin:0;">{{ $h['rule_name'] ?? ('Regla #'.($h['rule_id'] ?? '-')) }}</p>
                </td>
                <td style="padding:11px 16px;font-size:12px;color:var(--al-muted);">
                    {{ Str::limit($h['trigger_reason'] ?? '-', 55) }}
                    @if(!empty($h['trigger_value']))
                    <span style="font-weight:600;color:var(--al-text);"> = {{ $h['trigger_value'] }}</span>
                    @endif
                </td>
                <td style="padding:11px 16px;font-size:11px;color:var(--al-muted);">
                    <p style="margin:0;font-weight:600;color:var(--al-text);">{{ $h['task_name'] ?? '-' }}</p>
                    <p style="margin:2px 0 0;font-family:monospace;">{{ Str::limit($h['report_id'] ?? '', 25) }}</p>
                </td>
                <td style="padding:11px 16px;text-align:center;">
                    <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;background:{{ $wokBg }};color:{{ $wokTxt }};">
                        {{ $wokLabel }}
                    </span>
                </td>
                <td style="padding:11px 16px;text-align:center;font-size:12px;color:var(--al-muted);">
                    @if(isset($h['triggered_at']))
                        {{ \Carbon\Carbon::parse($h['triggered_at'])->format('d/m/Y') }}<br>
                        <span style="font-size:11px;">{{ \Carbon\Carbon::parse($h['triggered_at'])->format('H:i') }} UTC</span>
                    @else —
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p style="font-size:11px;color:var(--al-muted);margin:8px 0 0;">{{ count($history) }} disparo(s) registrado(s)</p>
@endif

@endif {{-- fin tabs --}}

</x-filament-panels::page>
