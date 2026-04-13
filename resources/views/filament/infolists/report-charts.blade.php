@php
    $kpis     = $getRecord()->kpis ?? [];
    $totals   = $kpis['totals'] ?? [];
    $critical = (int) ($totals['critical'] ?? $getRecord()->critical ?? 0);
    $high     = (int) ($totals['high']     ?? $getRecord()->high     ?? 0);
    $medium   = (int) ($totals['medium']   ?? $getRecord()->medium   ?? 0);
    $low      = (int) ($totals['low']      ?? $getRecord()->low      ?? 0);
    $info     = (int) ($totals['info']     ?? $getRecord()->info_count ?? 0);

    $topHosts = $kpis['hosts']['top'] ?? [];
    $topPorts = $kpis['ports']['top'] ?? [];
    $topCves  = $kpis['cves']['top']  ?? [];

    $timeline = $kpis['timeline'] ?? [];
    $duration = $timeline['duration_seconds'] ?? 0;
    $durationFmt = $duration > 0
        ? sprintf('%dh %02dm', intdiv($duration, 3600), intdiv($duration % 3600, 60))
        : 'N/A';

    $chartId   = 'rpt-' . $getRecord()->id;
    $hostsJson = json_encode(array_column($topHosts, 'value'));
    $hostsData = json_encode(array_column($topHosts, 'count'));
    $portsJson = json_encode(array_column($topPorts, 'value'));
    $portsData = json_encode(array_column($topPorts, 'count'));
    $totalSev  = $critical + $high + $medium + $low + $info;
@endphp

<div
    x-data="{
        chartInstances: {},
        initCharts() {
            if (!window.Chart) { setTimeout(() => this.initCharts(), 200); return; }
            const id  = '{{ $chartId }}';
            const sev = [{{ $critical }}, {{ $high }}, {{ $medium }}, {{ $low }}, {{ $info }}];
            const sevL = [
                'Critico ({{ $critical }})', 'Alto ({{ $high }})',
                'Medio ({{ $medium }})',     'Bajo ({{ $low }})', 'Info ({{ $info }})'
            ];
            const sevC = ['#dc2626','#ea580c','#d97706','#65a30d','#6b7280'];

            const mk = (elId, cfg) => {
                const el = document.getElementById(elId);
                if (!el) return;
                if (this.chartInstances[elId]) this.chartInstances[elId].destroy();
                this.chartInstances[elId] = new window.Chart(el, cfg);
            };

            mk(id + '-sev', {
                type: 'doughnut',
                data: { labels: sevL, datasets: [{ data: sev, backgroundColor: sevC, hoverOffset: 8, borderWidth: 2 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                    cutout: '62%'
                }
            });

            @if(count($topHosts) > 0)
            mk(id + '-hosts', {
                type: 'bar',
                data: {
                    labels: {!! $hostsJson !!},
                    datasets: [{ label: 'Vulnerabilidades', data: {!! $hostsData !!}, backgroundColor: '#3b82f6', borderRadius: 4 }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    scales: { x: { beginAtZero: true } },
                    plugins: { legend: { display: false } }
                }
            });
            @endif

            @if(count($topPorts) > 0)
            mk(id + '-ports', {
                type: 'bar',
                data: {
                    labels: {!! $portsJson !!},
                    datasets: [{ label: 'Vulns', data: {!! $portsData !!}, backgroundColor: '#8b5cf6', borderRadius: 4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { display: false } }
                }
            });
            @endif
        }
    }"
    x-init="$nextTick(() => initCharts())"
    class="space-y-5 py-2"
>

    {{-- Timeline strip --}}
    @if($timeline)
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
        @foreach([
            ['Inicio Escaneo', $timeline['start'] ?? 'N/A', '#2563eb'],
            ['Duracion',       $durationFmt,                '#0d9488'],
            ['Fin Escaneo',    $timeline['end']   ?? 'N/A', '#7c3aed'],
        ] as [$lbl, $val, $clr])
        <div style="background:white;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">{{ $lbl }}</div>
            <div style="font-size:12px;font-weight:600;color:{{ $clr }};">{{ $val }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Charts grid --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        {{-- Severity doughnut --}}
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
            <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">
                Distribucion por Severidad
                <span style="font-size:11px;color:#9ca3af;margin-left:6px;">(Total: {{ $totalSev }})</span>
            </div>
            <div style="height:220px;position:relative;">
                <canvas id="{{ $chartId }}-sev"></canvas>
            </div>
        </div>

        {{-- Top hosts --}}
        @if(count($topHosts) > 0)
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
            <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Top Hosts Vulnerables</div>
            <div style="height:220px;position:relative;">
                <canvas id="{{ $chartId }}-hosts"></canvas>
            </div>
        </div>
        @endif

        {{-- Top ports --}}
        @if(count($topPorts) > 0)
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
            <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Top Puertos Afectados</div>
            <div style="height:220px;position:relative;">
                <canvas id="{{ $chartId }}-ports"></canvas>
            </div>
        </div>
        @endif

        {{-- Top CVEs --}}
        @if(count($topCves) > 0)
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:16px;overflow:auto;">
            <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">
                Top CVEs
                <span style="font-size:11px;color:#9ca3af;margin-left:6px;">({{ $kpis['cves']['unique'] ?? 0 }} unicos)</span>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="border-bottom:2px solid #e5e7eb;">
                        <th style="text-align:left;padding:5px 8px;color:#6b7280;font-size:10px;text-transform:uppercase;">CVE</th>
                        <th style="text-align:right;padding:5px 8px;color:#6b7280;font-size:10px;text-transform:uppercase;">Casos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($topCves, 0, 10) as $cve)
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:5px 8px;font-family:monospace;color:#2563eb;">{{ $cve['value'] }}</td>
                        <td style="padding:5px 8px;text-align:right;font-weight:600;">{{ $cve['count'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
