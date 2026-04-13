<x-filament-panels::page>

<style>
    :root {
        --cv-card-bg: #ffffff;
        --cv-card-border: #e5e7eb;
        --cv-text: #111827;
        --cv-text-muted: #6b7280;
        --cv-text-light: #9ca3af;
        --cv-row-even: #ffffff;
        --cv-row-odd: #f9fafb;
        --cv-table-head-bg: #f9fafb;
        --cv-table-border: #e5e7eb;
        --cv-icon-bg: #f3f4f6;
    }
    .dark {
        --cv-card-bg: #1f2937;
        --cv-card-border: #374151;
        --cv-text: #f9fafb;
        --cv-text-muted: #9ca3af;
        --cv-text-light: #6b7280;
        --cv-row-even: #1f2937;
        --cv-row-odd: #111827;
        --cv-table-head-bg: #111827;
        --cv-table-border: #374151;
        --cv-icon-bg: #374151;
    }
</style>

    {{-- Status Header --}}
    @php
        if (!$checked) {
            $headerBg     = 'var(--cv-card-bg)';
            $headerBorder = 'var(--cv-card-border)';
            $iconBg       = '#e5e7eb';
            $titleStyle   = 'color:var(--cv-text-muted)';
            $subStyle     = 'color:var(--cv-text-light)';
        } elseif ($connected) {
            $headerBg     = '#f0fdf4';
            $headerBorder = '#86efac';
            $iconBg       = '#22c55e';
            $titleStyle   = 'color:#15803d';
            $subStyle     = 'color:#16a34a';
        } else {
            $headerBg     = '#fef2f2';
            $headerBorder = '#fca5a5';
            $iconBg       = '#ef4444';
            $titleStyle   = 'color:#b91c1c';
            $subStyle     = 'color:#dc2626';
        }
    @endphp

    <div style="background:{{ $headerBg }};border:1px solid {{ $headerBorder }};border-radius:16px;padding:20px;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="flex-shrink:0;">
                <div style="height:56px;width:56px;border-radius:50%;background:{{ $iconBg }};display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                    @if(!$checked)
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z"/>
                        </svg>
                    @elseif($connected)
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
            </div>
            <div>
                @if(!$checked)
                    <h2 style="margin:0;font-size:17px;font-weight:600;color:var(--cv-text);">Conexion sin verificar</h2>
                    <p style="margin:4px 0 0;font-size:13px;color:var(--cv-text-muted);">Haz clic en "Verificar Conexion" para comprobar el estado.</p>
                @elseif($connected)
                    <h2 style="margin:0;font-size:17px;font-weight:600;{{ $titleStyle }}">Conectado y autenticado</h2>
                    <p style="margin:4px 0 0;font-size:13px;{{ $subStyle }}">
                        OpenVAS accesible{{ $vmInfo ? ' — ' . $vmInfo : '' }}
                    </p>
                @else
                    <h2 style="margin:0;font-size:17px;font-weight:600;{{ $titleStyle }}">Sin conexion</h2>
                    <p style="margin:4px 0 0;font-size:13px;{{ $subStyle }};font-family:monospace;">{{ $errorMessage }}</p>
                @endif
            </div>
        </div>
    </div>

    @if($checked)
    {{-- Stats Grid --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px;">

        {{-- TCP Status --}}
        <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size:11px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;font-weight:600;">TCP</div>
            <div style="width:40px;height:40px;border-radius:50%;background:{{ $connected ? '#dcfce7' : '#fee2e2' }};display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="{{ $connected ? '#16a34a' : '#dc2626' }}" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.394c-3.807-3.807-3.807-9.98 0-13.788m13.788 0c3.807 3.807 3.807 9.981 0 13.788M12 12h.008v.008H12V12z"/>
                </svg>
            </div>
            <div style="font-size:12px;font-weight:600;color:{{ $connected ? '#16a34a' : '#dc2626' }};">{{ $connected ? 'Reachable' : 'Unreachable' }}</div>
        </div>

        {{-- Latencia --}}
        <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size:11px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;font-weight:600;">Latencia</div>
            <div style="font-size:26px;font-weight:700;color:#2563eb;line-height:1.1;">
                {{ $latencyMs !== null ? number_format($latencyMs, 1) : '—' }}
            </div>
            <div style="font-size:11px;color:var(--cv-text-light);margin-top:2px;">ms</div>
        </div>

        {{-- GMP Auth --}}
        <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size:11px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;font-weight:600;">GMP Auth</div>
            <div style="width:40px;height:40px;border-radius:50%;background:{{ $authenticated ? '#dcfce7' : '#fef9c3' }};display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="{{ $authenticated ? '#16a34a' : '#ca8a04' }}" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </div>
            <div style="font-size:12px;font-weight:600;color:{{ $authenticated ? '#16a34a' : '#ca8a04' }};">{{ $authenticated ? 'Autenticado' : 'Sin autenticar' }}</div>
        </div>

        {{-- OpenVAS Version --}}
        <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size:11px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;font-weight:600;">Version OpenVAS</div>
            <div style="font-size:13px;font-weight:700;color:#4f46e5;word-break:break-all;">
                {{ $openvasVersion ?: ($versionInfo['openvas'] ?? 'N/A') }}
            </div>
            @if($versionInfo['gmp'] ?? false)
            <div style="font-size:11px;color:var(--cv-text-light);margin-top:4px;">GMP: {{ $versionInfo['gmp'] }}</div>
            @endif
        </div>
    </div>

    {{-- Version Details --}}
    @if(!empty($versionInfo))
    <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <h3 style="margin:0 0 12px;font-size:13px;font-weight:600;color:var(--cv-text);display:flex;align-items:center;gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
            Informacion de Version
        </h3>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;font-size:13px;">
            @foreach($versionInfo as $key => $value)
            @if($value && !is_array($value))
            <div style="border-left:3px solid #6366f1;padding-left:10px;">
                <div style="font-size:10px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;">{{ $key }}</div>
                <div style="font-family:monospace;font-weight:600;color:var(--cv-text);margin-top:2px;">{{ $value }}</div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- History Table --}}
    @if(!empty($history))
    <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <h3 style="margin:0 0 12px;font-size:13px;font-weight:600;color:var(--cv-text);display:flex;align-items:center;gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Historial de Operaciones Recientes ({{ count($history) }})
        </h3>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:var(--cv-table-head-bg);border-bottom:2px solid var(--cv-table-border);">
                        <th style="text-align:left;padding:8px 10px;font-weight:600;color:var(--cv-text-muted);font-size:10px;text-transform:uppercase;">Accion</th>
                        <th style="text-align:left;padding:8px 10px;font-weight:600;color:var(--cv-text-muted);font-size:10px;text-transform:uppercase;">Recurso</th>
                        <th style="text-align:left;padding:8px 10px;font-weight:600;color:var(--cv-text-muted);font-size:10px;text-transform:uppercase;">Estado</th>
                        <th style="text-align:left;padding:8px 10px;font-weight:600;color:var(--cv-text-muted);font-size:10px;text-transform:uppercase;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $i => $entry)
                    @php $rowBg = $i % 2 === 0 ? 'var(--cv-row-even)' : 'var(--cv-row-odd)'; @endphp
                    <tr style="background:{{ $rowBg }};border-bottom:1px solid var(--cv-table-border);">
                        <td style="padding:8px 10px;font-weight:600;color:var(--cv-text);">{{ $entry['action'] ?? '—' }}</td>
                        <td style="padding:8px 10px;font-family:monospace;color:#4f46e5;">{{ $entry['resource_id'] ?? $entry['resource'] ?? '—' }}</td>
                        <td style="padding:8px 10px;">
                            @php $ok = ($entry['status'] ?? '') === 'success'; @endphp
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:10px;font-weight:600;background:{{ $ok ? '#dcfce7' : '#fee2e2' }};color:{{ $ok ? '#15803d' : '#b91c1c' }};">
                                {{ $entry['status'] ?? '—' }}
                            </span>
                        </td>
                        <td style="padding:8px 10px;color:var(--cv-text-muted);">{{ $entry['timestamp'] ?? $entry['created_at'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @else
    {{-- Not checked yet --}}
    <div style="background:var(--cv-card-bg);border:2px dashed var(--cv-card-border);border-radius:16px;padding:60px;text-align:center;">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--cv-icon-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="var(--cv-text-light)" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z"/>
            </svg>
        </div>
        <p style="font-size:14px;color:var(--cv-text-muted);margin:0;">
            Presiona "Verificar Conexion" para comprobar el estado de la conexion con la VM de OpenVAS.
        </p>
    </div>
    @endif

</x-filament-panels::page>
