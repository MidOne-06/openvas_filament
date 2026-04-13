<x-filament-panels::page>
@assets
<script src="/js/chart.min.js"></script>
@endassets
<style>
:root {
    --tr-bg:#ffffff; --tr-border:#e5e7eb; --tr-text:#111827;
    --tr-muted:#6b7280; --tr-row:#f9fafb; --tr-card:#ffffff;
    --tr-navy:#1e3a5f; --tr-teal:#0ea5e9;
}
.dark {
    --tr-bg:#1f2937; --tr-border:#374151; --tr-text:#f9fafb;
    --tr-muted:#9ca3af; --tr-row:#111827; --tr-card:#1f2937;
}
.tr-card { background:var(--tr-bg);border:1px solid var(--tr-border);border-radius:12px;padding:18px;margin-bottom:16px; }
.tr-kpi  { background:var(--tr-bg);border:1px solid var(--tr-border);border-radius:10px;padding:16px;text-align:center; }
.tr-title{ font-size:12px;font-weight:700;color:var(--tr-muted);text-transform:uppercase;letter-spacing:0.06em;margin:0 0 14px; }
</style>

@if(!$loaded)
<div class="tr-card" style="text-align:center;padding:48px;">
    <p style="color:var(--tr-muted);font-size:13px;">Cargando datos de tendencias...</p>
</div>
@elseif(count($globalTrends) === 0)
<div class="tr-card" style="text-align:center;padding:48px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.2" style="margin:0 auto 12px;display:block;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
    </svg>
    <p style="color:var(--tr-muted);font-size:13px;margin:0 0 12px;">No hay datos de tendencias todavia.</p>
    <p style="color:var(--tr-muted);font-size:12px;margin:0 0 14px;">Si subes reportes CSV, usa <strong>Backfill desde CSV</strong> en los botones de arriba.</p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
        <button onclick="Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('backfillCsv')"
                style="background:#6366f1;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;">
            Backfill desde CSV
        </button>
        <button onclick="Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('backfill')"
                style="background:#6b7280;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;">
            Backfill desde GVM
        </button>
    </div>
</div>
@else

@php
    // Agrupar metricas globales por fecha
    $byDate  = collect($globalTrends)->sortBy('date');
    $labels  = $byDate->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->values()->toArray();
    $totals  = $byDate->pluck('total')->values()->toArray();
    $crits   = $byDate->pluck('critical')->values()->toArray();
    $highs   = $byDate->pluck('high')->values()->toArray();
    $meds    = $byDate->pluck('medium')->values()->toArray();
    $lows    = $byDate->pluck('low')->values()->toArray();
    $avgCvss = $byDate->pluck('avg_cvss')->values()->toArray();

    $latestMetric = $byDate->last() ?? [];
    $totalScans   = $summary['total_scans'] ?? count($globalTrends);
@endphp

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:16px;">
    @foreach([
        ['Escaneos Registrados', $totalScans,                             '#1d4ed8'],
        ['Total Vulnerab.',      $latestMetric['total'] ?? '-',           '#1d4ed8'],
        ['Critico',              $latestMetric['critical']    ?? '-',     '#dc2626'],
        ['Alto',                 $latestMetric['high']        ?? '-',     '#ea580c'],
        ['CVSS Promedio',        number_format($latestMetric['avg_cvss'] ?? 0, 1), '#d97706'],
    ] as [$lbl, $val, $clr])
    <div class="tr-kpi">
        <div style="font-size:26px;font-weight:800;color:{{ $clr }};line-height:1.1;">{{ $val }}</div>
        <div style="font-size:10px;color:var(--tr-muted);text-transform:uppercase;letter-spacing:0.07em;margin-top:5px;font-weight:600;">{{ $lbl }}</div>
    </div>
    @endforeach
</div>

{{-- Graficos --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:16px;">
    {{-- Linea: evolucion de vulnerabilidades --}}
    <div class="tr-card">
        <p class="tr-title">Evolucion de Vulnerabilidades en el Tiempo</p>
        <div style="height:240px;position:relative;">
            <canvas id="tr-evolution"></canvas>
        </div>
    </div>
    {{-- Dona: distribucion actual --}}
    <div class="tr-card">
        <p class="tr-title">Distribucion Actual por Severidad</p>
        <div style="height:240px;position:relative;">
            <canvas id="tr-donut"></canvas>
        </div>
    </div>
</div>

{{-- Tabla de metricas historicas --}}
<div class="tr-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 16px;border-bottom:1px solid var(--tr-border);">
        <p class="tr-title" style="margin:0;">Historial de Metricas de Escaneo</p>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--tr-row);">
                    @foreach(['Tarea','Fecha','Total','Critico','Alto','Medio','Bajo','CVSS Prom.','CVSS Max'] as $h)
                    <th style="padding:9px 12px;font-size:11px;font-weight:700;color:var(--tr-muted);text-transform:uppercase;white-space:nowrap;{{ $loop->index > 0 ? 'text-align:center;' : 'text-align:left;' }}">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($byDate->reverse()->take(30) as $m)
                <tr style="border-top:1px solid var(--tr-border);">
                    <td style="padding:10px 12px;font-size:12px;color:var(--tr-text);">{{ Str::limit($m['task_name'] ?? '-', 30) }}</td>
                    <td style="padding:10px 12px;font-size:12px;color:var(--tr-muted);text-align:center;white-space:nowrap;">{{ \Carbon\Carbon::parse($m['date'] ?? now())->format('d/m/Y') }}</td>
                    <td style="padding:10px 12px;font-size:12px;font-weight:700;color:var(--tr-text);text-align:center;">{{ $m['total'] ?? 0 }}</td>
                    <td style="padding:10px 12px;font-size:12px;font-weight:700;color:#dc2626;text-align:center;">{{ $m['critical'] ?? 0 }}</td>
                    <td style="padding:10px 12px;font-size:12px;font-weight:700;color:#ea580c;text-align:center;">{{ $m['high'] ?? 0 }}</td>
                    <td style="padding:10px 12px;font-size:12px;color:#d97706;text-align:center;">{{ $m['medium'] ?? 0 }}</td>
                    <td style="padding:10px 12px;font-size:12px;color:#16a34a;text-align:center;">{{ $m['low'] ?? 0 }}</td>
                    <td style="padding:10px 12px;font-size:12px;color:var(--tr-muted);text-align:center;">{{ number_format($m['avg_cvss'] ?? 0, 1) }}</td>
                    <td style="padding:10px 12px;font-size:12px;font-weight:700;color:var(--tr-muted);text-align:center;">{{ number_format($m['max_cvss'] ?? 0, 1) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Top Hosts --}}
@if(count($hostTrends) > 0)
<div class="tr-card" style="margin-top:0;">
    <p class="tr-title">Top Hosts mas Vulnerables</p>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--tr-row);">
                    @foreach(['#','IP','Critico','Alto','Medio','Bajo','Total'] as $h)
                    <th style="padding:9px 12px;font-size:11px;font-weight:700;color:var(--tr-muted);text-transform:uppercase;text-align:{{ $loop->first ? 'center' : ($loop->index === 1 ? 'left' : 'center') }};">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach(collect($hostTrends)->take(15) as $i => $h)
                <tr style="border-top:1px solid var(--tr-border);">
                    <td style="padding:9px 12px;text-align:center;font-size:12px;color:var(--tr-muted);">{{ $i+1 }}</td>
                    <td style="padding:9px 12px;font-family:monospace;font-size:12px;color:var(--tr-text);">{{ $h['ip'] ?? $h['host'] ?? '-' }}</td>
                    <td style="padding:9px 12px;text-align:center;font-weight:700;color:#dc2626;font-size:12px;">{{ $h['critical'] ?? 0 }}</td>
                    <td style="padding:9px 12px;text-align:center;font-weight:700;color:#ea580c;font-size:12px;">{{ $h['high'] ?? 0 }}</td>
                    <td style="padding:9px 12px;text-align:center;color:#d97706;font-size:12px;">{{ $h['medium'] ?? 0 }}</td>
                    <td style="padding:9px 12px;text-align:center;color:#16a34a;font-size:12px;">{{ $h['low'] ?? 0 }}</td>
                    <td style="padding:9px 12px;text-align:center;font-weight:700;color:var(--tr-text);font-size:12px;">{{ $h['total'] ?? 0 }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gc = isDark ? '#374151' : '#e5e7eb';
    const tc = isDark ? '#9ca3af' : '#6b7280';

    const labels   = @json($labels);
    const totals   = @json($totals);
    const crits    = @json($crits);
    const highs    = @json($highs);
    const meds     = @json($meds);
    const lows     = @json($lows);
    const latestC  = crits.at(-1)  || 0;
    const latestH  = highs.at(-1)  || 0;
    const latestM  = meds.at(-1)   || 0;
    const latestL  = lows.at(-1)   || 0;

    // Grafico de linea: evolucion
    const evCtx = document.getElementById('tr-evolution');
    if (evCtx) {
        new Chart(evCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label:'Total',    data:totals, borderColor:'#6366f1', backgroundColor:'rgba(99,102,241,0.1)', tension:0.4, fill:true, pointRadius:3 },
                    { label:'Critico',  data:crits,  borderColor:'#dc2626', backgroundColor:'transparent', tension:0.4, pointRadius:2, borderDash:[4,3] },
                    { label:'Alto',     data:highs,  borderColor:'#ea580c', backgroundColor:'transparent', tension:0.4, pointRadius:2, borderDash:[4,3] },
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                scales: {
                    x: { ticks:{color:tc,font:{size:10}}, grid:{color:gc} },
                    y: { beginAtZero:true, ticks:{color:tc,font:{size:10}}, grid:{color:gc} }
                },
                plugins: { legend:{ labels:{color:tc,font:{size:11}} } }
            }
        });
    }

    // Grafico dona: distribucion actual
    const doCtx = document.getElementById('tr-donut');
    if (doCtx && (latestC + latestH + latestM + latestL) > 0) {
        new Chart(doCtx, {
            type: 'doughnut',
            data: {
                labels: ['Critico','Alto','Medio','Bajo'],
                datasets: [{ data:[latestC,latestH,latestM,latestL], backgroundColor:['#dc2626','#ea580c','#d97706','#16a34a'], borderWidth:2 }]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                plugins: { legend:{ position:'bottom', labels:{color:tc,font:{size:11},boxWidth:12} } }
            }
        });
    }
});
</script>

@endif
</x-filament-panels::page>
