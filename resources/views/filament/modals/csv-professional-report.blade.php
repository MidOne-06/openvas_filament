@php
    $apiBase = $apiBase ?? 'http://localhost:8000';

    $dist        = $metrics['severity_distribution'] ?? [];
    $critical    = (int) ($dist['critical'] ?? 0);
    $high        = (int) ($dist['high']     ?? 0);
    $medium      = (int) ($dist['medium']   ?? 0);
    $low         = (int) ($dist['low']      ?? 0);
    $total       = (int) ($metrics['total_vulnerabilities'] ?? 0);
    $uniqueHosts = (int) ($metrics['unique_hosts']          ?? 0);
    $avgCvss     = round((float) ($metrics['avg_cvss']  ?? 0), 1);
    $maxCvss     = round((float) ($metrics['max_cvss']  ?? 0), 1);

    $hostCounts = [];
    foreach ($rows as $r) {
        $ip = $r['IP'] ?? $r['ip'] ?? '';
        if ($ip) $hostCounts[$ip] = ($hostCounts[$ip] ?? 0) + 1;
    }
    arsort($hostCounts);
    $topHosts = array_slice($hostCounts, 0, 10, true);

    $portCounts = [];
    foreach ($rows as $r) {
        $p = $r['Port'] ?? $r['port'] ?? '';
        if ($p && $p !== '0') $portCounts[$p] = ($portCounts[$p] ?? 0) + 1;
    }
    arsort($portCounts);
    $topPorts = array_slice($portCounts, 0, 8, true);

    $cveCounts = [];
    foreach ($rows as $r) {
        $cves = $r['CVEs'] ?? $r['cves'] ?? '';
        if ($cves) {
            foreach (preg_split('/[\s,;]+/', $cves) as $cve) {
                $cve = trim($cve);
                if (str_starts_with($cve, 'CVE-')) {
                    $cveCounts[$cve] = ($cveCounts[$cve] ?? 0) + 1;
                }
            }
        }
    }
    arsort($cveCounts);
    $topCves    = array_slice($cveCounts, 0, 10, true);
    $maxCveCount = $topCves ? max(array_values($topCves)) : 1;

    $sevOrder = ['CRITICAL' => 0, 'HIGH' => 1, 'MEDIUM' => 2, 'LOW' => 3, 'INFO' => 4, 'LOG' => 5];
    usort($rows, function ($a, $b) use ($sevOrder) {
        $sa = strtoupper($a['Severity'] ?? $a['severity'] ?? '');
        $sb = strtoupper($b['Severity'] ?? $b['severity'] ?? '');
        $oa = $sevOrder[$sa] ?? 99;
        $ob = $sevOrder[$sb] ?? 99;
        if ($oa !== $ob) return $oa - $ob;
        return (float)($b['CVSS'] ?? $b['cvss'] ?? 0) <=> (float)($a['CVSS'] ?? $a['cvss'] ?? 0);
    });

    $rowsJson = json_encode(array_map(function($r) {
        return [
            'ip'       => $r['IP']       ?? $r['ip']       ?? '',
            'port'     => (string)($r['Port']     ?? $r['port']     ?? ''),
            'cvss'     => (float)($r['CVSS']      ?? $r['cvss']     ?? 0),
            'severity' => strtoupper($r['Severity'] ?? $r['severity'] ?? ''),
            'nvt'      => $r['NVT Name'] ?? $r['nvt_name'] ?? '',
            'cves'     => $r['CVEs']     ?? $r['cves']     ?? '',
            'hostname' => $r['Hostname'] ?? $r['hostname'] ?? '',
        ];
    }, $rows));

    $chartId     = 'cpm' . $record->id;
    $hostsLabels = count($topHosts) ? json_encode(array_keys($topHosts))   : '[]';
    $hostsValues = count($topHosts) ? json_encode(array_values($topHosts)) : '[]';
    $portsLabels = count($topPorts) ? json_encode(array_keys($topPorts))   : '[]';
    $portsValues = count($topPorts) ? json_encode(array_values($topPorts)) : '[]';
    $reportId    = $record->report_id ?? $record->id;
@endphp

@assets
<script src="/js/chart.min.js"></script>
@endassets

<style>
[x-cloak] { display: none !important; }

/* ===== Variables dark / light ===== */
.cpm-wrap {
    --cpm-bg:         #ffffff; --cpm-bg2:      #f9fafb;
    --cpm-border:     #e5e7eb; --cpm-text:     #111827;
    --cpm-text-muted: #6b7280; --cpm-text-xs:  #9ca3af;
    --cpm-row-a:      #ffffff; --cpm-row-b:    #f9fafb;
    --cpm-head-bg:    #f3f4f6; --cpm-head-border: #e5e7eb;
}
.dark .cpm-wrap {
    --cpm-bg:         #1f2937; --cpm-bg2:      #111827;
    --cpm-border:     #374151; --cpm-text:     #f9fafb;
    --cpm-text-muted: #9ca3af; --cpm-text-xs:  #6b7280;
    --cpm-row-a:      #1f2937; --cpm-row-b:    #111827;
    --cpm-head-bg:    #111827; --cpm-head-border: #374151;
}
.cpm-card  { background:var(--cpm-bg); border:1px solid var(--cpm-border); border-radius:12px; padding:16px; }
.cpm-title {
    font-size:12px; font-weight:700; color:var(--cpm-text); margin:0 0 14px;
    text-transform:uppercase; letter-spacing:0.06em;
    display:flex; align-items:center; gap:8px;
}
.cpm-title::before {
    content:''; display:inline-block; width:3px; height:14px;
    background:linear-gradient(180deg,#3b82f6,#8b5cf6); border-radius:2px;
}

/* ===== Tabla base ===== */
.cpm-tbl { width:100%; border-collapse:collapse; font-size:12px; }
.cpm-tbl thead { position:sticky; top:0; z-index:2; }
.cpm-tbl thead tr { background:var(--cpm-head-bg); border-bottom:2px solid var(--cpm-head-border); }
.cpm-tbl th { padding:8px 10px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--cpm-text-muted); white-space:nowrap; }
.cpm-tbl td { padding:7px 10px; border-bottom:1px solid var(--cpm-border); color:var(--cpm-text); vertical-align:middle; }
.cpm-tbl tbody tr:nth-child(odd)  { background:var(--cpm-row-a); }
.cpm-tbl tbody tr:nth-child(even) { background:var(--cpm-row-b); }
.cpm-tbl tbody tr:hover { background:color-mix(in srgb, var(--cpm-border) 30%, transparent); }

/* ===== CVE table compacta ===== */
.cpm-cve-tbl { width:100%; border-collapse:collapse; font-size:12px; }
.cpm-cve-tbl tr { border-bottom:1px solid var(--cpm-border); }
.cpm-cve-tbl tr:last-child { border-bottom:none; }
.cpm-cve-tbl tr:nth-child(odd)  { background:var(--cpm-row-a); }
.cpm-cve-tbl tr:nth-child(even) { background:var(--cpm-row-b); }
.cpm-cve-tbl td { padding:6px 10px; color:var(--cpm-text); vertical-align:middle; }

/* ===== Badges CVE ===== */
.cpm-cve-badge {
    display:inline-block; font-family:monospace; font-size:10px; font-weight:600;
    padding:1px 6px; border-radius:4px;
    background:rgba(59,130,246,0.1); color:#3b82f6;
    border:1px solid rgba(59,130,246,0.25);
    margin:1px 2px 1px 0;
    white-space:nowrap;
}

/* ===== Filter bar ===== */
.cpm-filter-bar {
    position:sticky; top:0; z-index:10;
    background:var(--cpm-bg);
    border-bottom:1px solid var(--cpm-border);
    padding:10px 14px;
    display:flex; flex-wrap:wrap; align-items:center; gap:8px;
    border-top-left-radius:12px; border-top-right-radius:12px;
}
.cpm-filter-btn {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:600;
    padding:4px 11px; border-radius:999px;
    border:1px solid var(--cpm-border);
    background:transparent;
    color:var(--cpm-text-muted);
    cursor:pointer;
    transition:background 0.15s, color 0.15s, border-color 0.15s;
    white-space:nowrap;
    line-height:1.4;
}
.cpm-filter-btn:hover { background:color-mix(in srgb, var(--cpm-border) 40%, transparent); color:var(--cpm-text); }
.cpm-filter-btn.active          { background:#3b82f6; color:#fff; border-color:#3b82f6; }
.cpm-filter-btn.active-critical { background:#dc2626; color:#fff; border-color:#dc2626; }
.cpm-filter-btn.active-high     { background:#ea580c; color:#fff; border-color:#ea580c; }
.cpm-filter-btn.active-medium   { background:#d97706; color:#fff; border-color:#d97706; }
.cpm-filter-btn.active-low      { background:#65a30d; color:#fff; border-color:#65a30d; }
.cpm-filter-input {
    font-size:12px; padding:4px 10px;
    border-radius:8px; border:1px solid var(--cpm-border);
    background:var(--cpm-bg2); color:var(--cpm-text);
    outline:none;
    transition:border-color 0.15s, box-shadow 0.15s;
    min-width:0;
}
.cpm-filter-input:focus { border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,0.2); }
.cpm-filter-input::placeholder { color:var(--cpm-text-xs); }
.cpm-results-count {
    font-size:11px; color:var(--cpm-text-muted);
    margin-left:auto; white-space:nowrap;
}

/* ===== Pagination ===== */
.cpm-pagination {
    display:flex; align-items:center; justify-content:center; gap:8px;
    padding:10px 16px 4px;
    border-top:1px solid var(--cpm-border);
}
.cpm-page-btn {
    font-size:11px; font-weight:600;
    padding:4px 12px; border-radius:8px;
    border:1px solid var(--cpm-border);
    background:var(--cpm-bg2); color:var(--cpm-text);
    cursor:pointer;
    transition:background 0.15s, opacity 0.15s;
}
.cpm-page-btn:hover:not(:disabled) { background:color-mix(in srgb, var(--cpm-border) 50%, transparent); }
.cpm-page-btn:disabled { opacity:0.4; cursor:default; }
.cpm-page-info { font-size:11px; color:var(--cpm-text-muted); }

/* ===== Host panel (slide-over) ===== */
.cpm-overlay {
    position:fixed; inset:0; z-index:9998;
    background:rgba(0,0,0,0.45);
    transition:opacity 0.3s ease;
}
.cpm-host-panel {
    position:fixed; right:0; top:0; height:100vh; width:420px;
    z-index:9999;
    background:var(--cpm-bg);
    border-left:1px solid var(--cpm-border);
    overflow-y:auto;
    transform:translateX(100%);
    transition:transform 0.3s ease;
    display:flex; flex-direction:column;
    box-shadow:-4px 0 24px rgba(0,0,0,0.12);
}
@media (min-width:900px) {
    .cpm-host-panel { width:480px; }
}
.cpm-host-panel.is-open { transform:translateX(0); }
.cpm-panel-header {
    position:sticky; top:0; z-index:2;
    background:var(--cpm-bg);
    border-bottom:1px solid var(--cpm-border);
    padding:14px 16px;
    display:flex; align-items:center; justify-content:space-between; gap:8px;
}
.cpm-panel-title {
    font-size:13px; font-weight:700; color:var(--cpm-text);
    text-transform:uppercase; letter-spacing:0.05em;
    display:flex; align-items:center; gap:8px;
}
.cpm-panel-title::before {
    content:''; display:inline-block; width:3px; height:14px;
    background:linear-gradient(180deg,#3b82f6,#8b5cf6); border-radius:2px;
    flex-shrink:0;
}
.cpm-panel-close {
    display:flex; align-items:center; justify-content:center;
    width:28px; height:28px; border-radius:6px;
    border:1px solid var(--cpm-border); background:var(--cpm-bg2);
    color:var(--cpm-text-muted);
    cursor:pointer; font-size:14px; line-height:1; flex-shrink:0;
    transition:background 0.15s, color 0.15s;
}
.cpm-panel-close:hover { background:color-mix(in srgb, var(--cpm-border) 50%, transparent); color:var(--cpm-text); }
.cpm-panel-body { padding:16px; flex:1; }
.cpm-kpi-mini {
    display:grid; grid-template-columns:repeat(3,1fr); gap:8px;
    margin-bottom:14px;
}
.cpm-kpi-mini-item {
    background:var(--cpm-bg2); border:1px solid var(--cpm-border);
    border-radius:8px; padding:8px 10px; text-align:center;
}
.cpm-kpi-mini-val { font-size:18px; font-weight:800; color:var(--cpm-text); line-height:1.1; }
.cpm-kpi-mini-lbl { font-size:10px; color:var(--cpm-text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-top:2px; }
.cpm-spinner {
    display:flex; align-items:center; justify-content:center;
    padding:40px; color:var(--cpm-text-muted); font-size:12px; gap:8px;
}
.cpm-spinner-ring {
    width:20px; height:20px; border-radius:50%;
    border:2px solid var(--cpm-border);
    border-top-color:#3b82f6;
    animation:cpm-spin 0.75s linear infinite; flex-shrink:0;
}
@keyframes cpm-spin { to { transform:rotate(360deg); } }
</style>

<div
    class="cpm-wrap space-y-4 py-1"
    x-data="{
        /* ---- Charts ---- */
        initCharts() {
            const cid     = '{{ $chartId }}';
            const sevData = [{{ $critical }}, {{ $high }}, {{ $medium }}, {{ $low }}];

            const doInit = () => {
                if (!window.Chart) { setTimeout(doInit, 250); return; }
                if (!document.getElementById(cid + '-sev')) { setTimeout(doInit, 250); return; }

                const isDark = document.documentElement.classList.contains('dark');
                const tc  = isDark ? '#9ca3af' : '#6b7280';
                const gc  = isDark ? '#374151' : '#e5e7eb';
                const bg2 = isDark ? '#1f2937' : '#ffffff';

                const mk = (canvasId, cfg) => {
                    const el = document.getElementById(canvasId);
                    if (!el) return;
                    if (el._chart) el._chart.destroy();
                    el._chart = new Chart(el, cfg);
                };

                /* ---- Doughnut severidad ---- */
                mk(cid + '-sev', {
                    type: 'doughnut',
                    data: {
                        labels: ['Critico', 'Alto', 'Medio', 'Bajo'],
                        datasets: [{
                            data: sevData,
                            backgroundColor: ['#dc2626','#ea580c','#d97706','#65a30d'],
                            borderColor: bg2, borderWidth: 3, hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '62%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, boxHeight: 10, padding: 12, font: { size: 11 }, color: tc }
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' vuln.'
                                }
                            }
                        }
                    }
                });

                /* ---- Bar horizontal: top hosts (colores por riesgo) ---- */
                const he = document.getElementById(cid + '-hosts');
                if (he && he.dataset.labels) {
                    const hLabels = JSON.parse(he.dataset.labels);
                    const hData   = JSON.parse(he.dataset.values);
                    const riskPalette = [
                        '#dc2626','#ea580c','#d97706','#ca8a04','#65a30d',
                        '#0d9488','#0284c7','#2563eb','#4f46e5','#7c3aed'
                    ];
                    const hColors = hData.map((_, i) => riskPalette[i] || '#3b82f6');

                    mk(cid + '-hosts', {
                        type: 'bar',
                        data: {
                            labels: hLabels,
                            datasets: [{
                                label: 'Vulnerabilidades',
                                data: hData,
                                backgroundColor: hColors,
                                borderRadius: 6,
                                borderSkipped: false,
                                barPercentage: 0.72,
                                categoryPercentage: 0.85
                            }]
                        },
                        options: {
                            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: { color: tc, font: { size: 10 } },
                                    grid: { color: gc }
                                },
                                y: {
                                    ticks: { color: tc, font: { size: 10 }, crossAlign: 'far' },
                                    grid: { color: 'transparent' }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => '  ' + ctx.parsed.x + ' vulnerabilidades'
                                    }
                                }
                            },
                            onClick: (e, els) => {
                                if (!els.length) return;
                                const ip = hLabels[els[0].index];
                                $dispatch('open-host-panel-{{ $chartId }}', { ip });
                            }
                        }
                    });
                }

                /* ---- Bar vertical: top puertos ---- */
                const pe = document.getElementById(cid + '-ports');
                if (pe && pe.dataset.labels) {
                    mk(cid + '-ports', {
                        type: 'bar',
                        data: {
                            labels: JSON.parse(pe.dataset.labels),
                            datasets: [{
                                label: 'Exposiciones',
                                data: JSON.parse(pe.dataset.values),
                                backgroundColor: '#8b5cf6',
                                borderRadius: 6,
                                borderSkipped: false,
                                barPercentage: 0.65
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, ticks: { color: tc }, grid: { color: gc } },
                                x: { ticks: { color: tc }, grid: { color: 'transparent' } }
                            },
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            };

            doInit();
        },

        /* ---- Filters & pagination ---- */
        allRows: {{ $rowsJson }},
        filterSev: 'ALL',
        searchText: '',
        cvssMin: 0,
        currentPage: 1,
        pageSize: 25,

        get filteredRows() {
            const sev  = this.filterSev;
            const txt  = this.searchText.toLowerCase().trim();
            const cmin = parseFloat(this.cvssMin) || 0;
            return this.allRows.filter(r => {
                if (sev !== 'ALL' && r.severity !== sev) return false;
                if (cmin > 0 && r.cvss < cmin) return false;
                if (txt) {
                    const inIp  = r.ip.toLowerCase().includes(txt);
                    const inNvt = r.nvt.toLowerCase().includes(txt);
                    if (!inIp && !inNvt) return false;
                }
                return true;
            });
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredRows.length / this.pageSize));
        },
        get paginatedRows() {
            const start = (this.currentPage - 1) * this.pageSize;
            return this.filteredRows.slice(start, start + this.pageSize);
        },
        setFilter(sev) {
            this.filterSev   = sev;
            this.currentPage = 1;
        },
        prevPage() { if (this.currentPage > 1) this.currentPage--; },
        nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
        filterBtnClass(sev) {
            if (this.filterSev !== sev) return 'cpm-filter-btn';
            const m = {
                ALL:      'cpm-filter-btn active',
                CRITICAL: 'cpm-filter-btn active-critical',
                HIGH:     'cpm-filter-btn active-high',
                MEDIUM:   'cpm-filter-btn active-medium',
                LOW:      'cpm-filter-btn active-low'
            };
            return m[sev] || 'cpm-filter-btn active';
        },

        /* ---- Host panel ---- */
        hostPanelOpen: false,
        hostPanelIp: '',
        hostPanelData: null,
        hostPanelLoading: false,
        hostPanelError: '',

        openHostPanel(ip) {
            this.hostPanelIp      = ip;
            this.hostPanelOpen    = true;
            this.hostPanelData    = null;
            this.hostPanelError   = '';
            this.hostPanelLoading = true;
            const apiBase  = '{{ $apiBase }}';
            const reportId = '{{ $reportId }}';
            fetch(apiBase + '/csv/report/' + reportId + '/host/' + encodeURIComponent(ip))
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    this.hostPanelData    = data;
                    this.hostPanelLoading = false;
                })
                .catch(err => {
                    this.hostPanelError   = 'No se pudo cargar el detalle: ' + err.message;
                    this.hostPanelLoading = false;
                });
        },
        closeHostPanel() {
            this.hostPanelOpen = false;
            this.hostPanelIp   = '';
            this.hostPanelData = null;
        }
    }"
    x-init="setTimeout(() => initCharts(), 300)"
    @open-host-panel-{{ $chartId }}.window="openHostPanel($event.detail.ip)"
>

    {{-- ===== Banner PDF ===== --}}
    <div style="display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#eff6ff,#eef2ff);border:1px solid #c7d2fe;border-radius:12px;padding:13px 18px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 002.25 2.25h.75"/>
                </svg>
            </div>
            <div>
                <p style="margin:0;font-size:13px;font-weight:700;color:#1e40af;">{{ $record->original_filename ?: 'Reporte CSV' }}</p>
                <p style="margin:2px 0 0;font-size:11px;color:#6366f1;">ID: {{ $record->report_id }}</p>
            </div>
        </div>
        <a href="{{ $pdfUrl }}" target="_blank"
           style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#dc2626,#b91c1c);color:white;padding:9px 18px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;box-shadow:0 2px 8px rgba(220,38,38,0.35);">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Descargar PDF
        </a>
    </div>

    {{-- ===== KPI Cards ===== --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
        @foreach([
            ['Total Vuln.',  number_format($total),             '#1d4ed8', '#dbeafe', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['Hosts Unicos', $uniqueHosts,                      '#0f766e', '#ccfbf1', 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2'],
            ['CVSS Prom.',   $avgCvss > 0 ? $avgCvss : 'N/A',  '#b45309', '#fef3c7', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['CVSS Max',     $maxCvss  > 0 ? $maxCvss  : 'N/A','#be123c', '#ffe4e6', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
        ] as [$lbl, $val, $clr, $bgClr, $svgPath])
        <div class="cpm-card" style="text-align:center;padding:14px 10px;">
            <div style="width:36px;height:36px;border-radius:10px;background:{{ $bgClr }};display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="{{ $clr }}" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $svgPath }}"/>
                </svg>
            </div>
            <div style="font-size:22px;font-weight:800;color:{{ $clr }};line-height:1;">{{ $val }}</div>
            <div style="font-size:10px;color:var(--cpm-text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-top:5px;font-weight:600;">{{ $lbl }}</div>
        </div>
        @endforeach
    </div>

    {{-- ===== Severity Badges ===== --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
        @foreach([
            ['CRITICO', $critical, '#dc2626', '#fef2f2', '#fca5a5'],
            ['ALTO',    $high,     '#ea580c', '#fff7ed', '#fdba74'],
            ['MEDIO',   $medium,   '#d97706', '#fffbeb', '#fcd34d'],
            ['BAJO',    $low,      '#16a34a', '#f0fdf4', '#86efac'],
        ] as [$lbl, $cnt, $txtClr, $bgClr, $borderClr])
        <div style="background:{{ $bgClr }};border:1px solid {{ $borderClr }};border-radius:10px;padding:12px 10px;text-align:center;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-8px;right:-8px;width:40px;height:40px;border-radius:50%;background:{{ $borderClr }};opacity:0.3;"></div>
            <div style="font-size:30px;font-weight:800;color:{{ $txtClr }};line-height:1.05;position:relative;">{{ $cnt }}</div>
            <div style="font-size:10px;font-weight:700;color:{{ $txtClr }};text-transform:uppercase;letter-spacing:0.1em;margin-top:5px;position:relative;">{{ $lbl }}</div>
        </div>
        @endforeach
    </div>

    {{-- ===== Fila: Severidad + Top Hosts ===== --}}
    <div style="display:grid;grid-template-columns:5fr 7fr;gap:14px;">

        {{-- Doughnut --}}
        <div class="cpm-card">
            <p class="cpm-title">Distribucion por Severidad</p>
            <div style="height:230px;position:relative;">
                <canvas id="{{ $chartId }}-sev"></canvas>
            </div>
        </div>

        {{-- Top Hosts --}}
        @if(count($topHosts) > 0)
        <div class="cpm-card">
            <p class="cpm-title">
                Top Hosts Vulnerables
                <span style="font-size:10px;color:var(--cpm-text-muted);font-weight:400;margin-left:auto;text-transform:none;letter-spacing:0;">(click en barra para detalle)</span>
            </p>
            <div style="height:230px;position:relative;">
                <canvas id="{{ $chartId }}-hosts"
                    data-labels="{{ $hostsLabels }}"
                    data-values="{{ $hostsValues }}"
                ></canvas>
            </div>
        </div>
        @else
        <div class="cpm-card" style="display:flex;align-items:center;justify-content:center;">
            <p style="color:var(--cpm-text-muted);font-size:13px;margin:0;">Sin datos de hosts</p>
        </div>
        @endif
    </div>

    {{-- ===== Top Puertos ===== --}}
    @if(count($topPorts) > 0)
    <div class="cpm-card">
        <p class="cpm-title">Top Puertos Afectados</p>
        <div style="height:155px;position:relative;">
            <canvas id="{{ $chartId }}-ports"
                data-labels="{{ $portsLabels }}"
                data-values="{{ $portsValues }}"
            ></canvas>
        </div>
    </div>
    @endif

    {{-- ===== Top CVEs — layout compacto con barra visual ===== --}}
    @if(count($topCves) > 0)
    <div class="cpm-card">
        <p class="cpm-title">Top CVEs Detectados</p>
        <table class="cpm-cve-tbl">
            <thead>
                <tr style="border-bottom:2px solid var(--cpm-head-border);background:var(--cpm-head-bg);">
                    <td style="padding:6px 10px;width:28px;font-size:10px;font-weight:700;color:var(--cpm-text-muted);text-transform:uppercase;">#</td>
                    <td style="padding:6px 10px;font-size:10px;font-weight:700;color:var(--cpm-text-muted);text-transform:uppercase;">CVE</td>
                    <td style="padding:6px 10px;font-size:10px;font-weight:700;color:var(--cpm-text-muted);text-transform:uppercase;white-space:nowrap;">Frecuencia</td>
                    <td style="padding:6px 10px;width:50px;text-align:right;font-size:10px;font-weight:700;color:var(--cpm-text-muted);text-transform:uppercase;">Total</td>
                </tr>
            </thead>
            <tbody>
                @foreach($topCves as $cve => $cnt)
                @php $pct = round($cnt / $maxCveCount * 100); @endphp
                <tr>
                    <td style="padding:6px 10px;font-size:11px;color:var(--cpm-text-muted);font-weight:600;">{{ $loop->iteration }}</td>
                    <td style="padding:6px 10px;font-family:monospace;font-size:12px;font-weight:600;color:#3b82f6;">{{ $cve }}</td>
                    <td style="padding:6px 10px;">
                        <div style="height:8px;background:var(--cpm-border);border-radius:4px;overflow:hidden;min-width:80px;">
                            <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#3b82f6,#8b5cf6);border-radius:4px;"></div>
                        </div>
                    </td>
                    <td style="padding:6px 10px;text-align:right;font-weight:700;font-size:13px;color:var(--cpm-text);">{{ $cnt }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ===== Detalle de Vulnerabilidades (con filtros + paginacion) ===== --}}
    <div class="cpm-card" style="padding:0; overflow:hidden;">

        {{-- Sticky filter bar --}}
        <div class="cpm-filter-bar">
            <span style="font-size:11px; font-weight:700; color:var(--cpm-text); text-transform:uppercase; letter-spacing:.05em; white-space:nowrap;">Severidad:</span>

            <button :class="filterBtnClass('ALL')"      @click="setFilter('ALL')">Todos</button>
            <button :class="filterBtnClass('CRITICAL')" @click="setFilter('CRITICAL')">Critico</button>
            <button :class="filterBtnClass('HIGH')"     @click="setFilter('HIGH')">Alto</button>
            <button :class="filterBtnClass('MEDIUM')"   @click="setFilter('MEDIUM')">Medio</button>
            <button :class="filterBtnClass('LOW')"      @click="setFilter('LOW')">Bajo</button>

            <input
                type="text"
                class="cpm-filter-input"
                style="width:160px;"
                placeholder="Buscar IP o NVT..."
                x-model.debounce.300ms="searchText"
                @input="currentPage = 1"
            >

            <div style="display:flex; align-items:center; gap:5px;">
                <span style="font-size:11px; color:var(--cpm-text-muted); white-space:nowrap;">CVSS &ge;:</span>
                <input
                    type="number"
                    class="cpm-filter-input"
                    style="width:60px;"
                    min="0" max="10" step="0.1"
                    x-model.number.debounce.300ms="cvssMin"
                    @input="currentPage = 1"
                >
            </div>

            <span class="cpm-results-count">
                Mostrando
                <strong x-text="Math.min(currentPage * pageSize, filteredRows.length) - (currentPage - 1) * pageSize"></strong>
                de
                <strong x-text="filteredRows.length"></strong>
                vulnerabilidades
            </span>
        </div>

        {{-- Table --}}
        <div style="overflow-x:auto; max-height:420px; overflow-y:auto;">
            <table class="cpm-tbl" style="table-layout:fixed;">
                <colgroup>
                    <col style="width:120px;">
                    <col style="width:130px;">
                    <col style="width:70px;">
                    <col style="width:90px;">
                    <col style="width:58px;">
                    <col style="width:300px;">
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th style="text-align:left;">IP</th>
                        <th style="text-align:left;">Hostname</th>
                        <th style="text-align:center;">Puerto</th>
                        <th style="text-align:left;">Severidad</th>
                        <th style="text-align:center;">CVSS</th>
                        <th style="text-align:left;">Vulnerabilidad</th>
                        <th style="text-align:left;">CVEs</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, idx) in paginatedRows" :key="idx">
                        <tr>
                            <td style="font-family:monospace; font-size:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" x-text="row.ip"></td>
                            <td style="font-size:11px; color:var(--cpm-text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" :title="row.hostname" x-text="row.hostname || '-'"></td>
                            <td style="font-family:monospace; font-size:11px; text-align:center; white-space:nowrap;" x-text="row.port || '-'"></td>
                            <td>
                                <span
                                    x-text="row.severity || '-'"
                                    :style="{
                                        display:'inline-block', fontSize:'10px', fontWeight:'700',
                                        padding:'2px 8px', borderRadius:'999px',
                                        background: row.severity === 'CRITICAL' ? 'rgba(220,38,38,0.12)'
                                                  : row.severity === 'HIGH'     ? 'rgba(234,88,12,0.12)'
                                                  : row.severity === 'MEDIUM'   ? 'rgba(217,119,6,0.12)'
                                                  : row.severity === 'LOW'      ? 'rgba(101,163,13,0.12)'
                                                  : 'rgba(107,114,128,0.12)',
                                        color: row.severity === 'CRITICAL' ? '#dc2626'
                                             : row.severity === 'HIGH'     ? '#ea580c'
                                             : row.severity === 'MEDIUM'   ? '#d97706'
                                             : row.severity === 'LOW'      ? '#65a30d'
                                             : '#6b7280',
                                        border: '1px solid',
                                        borderColor: row.severity === 'CRITICAL' ? 'rgba(220,38,38,0.25)'
                                                   : row.severity === 'HIGH'     ? 'rgba(234,88,12,0.25)'
                                                   : row.severity === 'MEDIUM'   ? 'rgba(217,119,6,0.25)'
                                                   : row.severity === 'LOW'      ? 'rgba(101,163,13,0.25)'
                                                   : 'rgba(107,114,128,0.25)'
                                    }"
                                ></span>
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <span
                                    x-text="row.cvss > 0 ? row.cvss.toFixed(1) : '-'"
                                    :style="{
                                        fontWeight:'700', fontSize:'12px',
                                        color: row.cvss >= 9 ? '#dc2626'
                                             : row.cvss >= 7 ? '#ea580c'
                                             : row.cvss >= 4 ? '#d97706'
                                             : row.cvss >  0 ? '#65a30d'
                                             : '#9ca3af'
                                    }"
                                ></span>
                            </td>
                            <td style="font-size:11px; white-space:normal; word-break:break-word; line-height:1.45;" x-text="row.nvt || '-'"></td>
                            <td style="font-size:11px; white-space:normal; line-height:1.6;">
                                <template x-if="row.cves">
                                    <div style="display:flex; flex-wrap:wrap; gap:2px;">
                                        <template x-for="cve in row.cves.split(/[\s,;]+/).filter(c => c.startsWith('CVE-'))" :key="cve">
                                            <span class="cpm-cve-badge" x-text="cve"></span>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!row.cves">
                                    <span style="color:var(--cpm-text-xs);">-</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                    <template x-if="paginatedRows.length === 0">
                        <tr>
                            <td colspan="7" style="text-align:center; padding:28px; color:var(--cpm-text-muted); font-size:12px;">
                                No se encontraron vulnerabilidades con los filtros aplicados.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="cpm-pagination">
            <button
                class="cpm-page-btn"
                :disabled="currentPage <= 1"
                @click="prevPage()"
            >&laquo; Anterior</button>

            <span class="cpm-page-info">
                Pagina <strong x-text="currentPage"></strong> de <strong x-text="totalPages"></strong>
            </span>

            <button
                class="cpm-page-btn"
                :disabled="currentPage >= totalPages"
                @click="nextPage()"
            >Siguiente &raquo;</button>
        </div>

    </div>

    {{-- ===== Host Detail Panel (slide-over) ===== --}}

    {{-- Semi-transparent overlay --}}
    <div
        class="cpm-overlay"
        x-cloak
        x-show="hostPanelOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeHostPanel()"
    ></div>

    {{-- Slide-over panel --}}
    <div
        class="cpm-host-panel"
        x-cloak
        :class="hostPanelOpen ? 'is-open' : ''"
    >
        <div class="cpm-panel-header">
            <div class="cpm-panel-title">
                Detalle:&nbsp;<span x-text="hostPanelIp" style="font-family:monospace; font-size:12px;"></span>
            </div>
            <button class="cpm-panel-close" @click="closeHostPanel()" title="Cerrar">&#x2715;</button>
        </div>

        <div class="cpm-panel-body">

            {{-- Loading --}}
            <div class="cpm-spinner" x-show="hostPanelLoading">
                <div class="cpm-spinner-ring"></div>
                <span>Cargando datos...</span>
            </div>

            {{-- Error --}}
            <div
                x-show="!hostPanelLoading && hostPanelError"
                style="padding:14px 16px; background:rgba(220,38,38,0.08); border:1px solid rgba(220,38,38,0.25); border-radius:8px; color:#dc2626; font-size:12px;"
                x-text="hostPanelError"
            ></div>

            {{-- Content --}}
            <div x-show="!hostPanelLoading && !hostPanelError && hostPanelData">

                {{-- KPI mini grid --}}
                <div class="cpm-kpi-mini">
                    <div class="cpm-kpi-mini-item">
                        <div class="cpm-kpi-mini-val" x-text="hostPanelData?.total ?? '-'"></div>
                        <div class="cpm-kpi-mini-lbl">Total</div>
                    </div>
                    <div class="cpm-kpi-mini-item" style="border-top:2px solid #dc2626;">
                        <div class="cpm-kpi-mini-val" style="color:#dc2626;" x-text="hostPanelData?.critical ?? 0"></div>
                        <div class="cpm-kpi-mini-lbl">Critico</div>
                    </div>
                    <div class="cpm-kpi-mini-item" style="border-top:2px solid #ea580c;">
                        <div class="cpm-kpi-mini-val" style="color:#ea580c;" x-text="hostPanelData?.high ?? 0"></div>
                        <div class="cpm-kpi-mini-lbl">Alto</div>
                    </div>
                    <div class="cpm-kpi-mini-item" style="border-top:2px solid #d97706;">
                        <div class="cpm-kpi-mini-val" style="color:#d97706;" x-text="hostPanelData?.medium ?? 0"></div>
                        <div class="cpm-kpi-mini-lbl">Medio</div>
                    </div>
                    <div class="cpm-kpi-mini-item" style="border-top:2px solid #65a30d;">
                        <div class="cpm-kpi-mini-val" style="color:#65a30d;" x-text="hostPanelData?.low ?? 0"></div>
                        <div class="cpm-kpi-mini-lbl">Bajo</div>
                    </div>
                    <div class="cpm-kpi-mini-item" style="border-top:2px solid #8b5cf6;">
                        <div class="cpm-kpi-mini-val" style="color:#8b5cf6;"
                             x-text="hostPanelData?.avg_cvss !== undefined ? parseFloat(hostPanelData.avg_cvss).toFixed(1) : '-'"></div>
                        <div class="cpm-kpi-mini-lbl">CVSS Prom.</div>
                    </div>
                </div>

                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--cpm-text-muted); margin-bottom:8px;">
                    Vulnerabilidades del host
                </div>

                <div style="overflow-x:auto; border:1px solid var(--cpm-border); border-radius:8px; overflow:hidden;">
                    <table class="cpm-tbl" style="font-size:11px;">
                        <thead>
                            <tr>
                                <th>Puerto</th>
                                <th>Severidad</th>
                                <th style="text-align:center;">CVSS</th>
                                <th>Vulnerabilidad</th>
                                <th>CVEs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="hostPanelData?.rows && hostPanelData.rows.length > 0">
                                <template x-for="(hr, hi) in hostPanelData.rows" :key="hi">
                                    <tr>
                                        <td style="font-family:monospace; white-space:nowrap;" x-text="hr.port || '-'"></td>
                                        <td>
                                            <span
                                                x-text="(hr.severity || '').toUpperCase() || '-'"
                                                :style="{
                                                    display:'inline-block', fontSize:'10px', fontWeight:'700',
                                                    padding:'2px 8px', borderRadius:'999px',
                                                    background: (hr.severity||'').toUpperCase() === 'CRITICAL' ? 'rgba(220,38,38,0.12)'
                                                              : (hr.severity||'').toUpperCase() === 'HIGH'     ? 'rgba(234,88,12,0.12)'
                                                              : (hr.severity||'').toUpperCase() === 'MEDIUM'   ? 'rgba(217,119,6,0.12)'
                                                              : (hr.severity||'').toUpperCase() === 'LOW'      ? 'rgba(101,163,13,0.12)'
                                                              : 'rgba(107,114,128,0.12)',
                                                    color: (hr.severity||'').toUpperCase() === 'CRITICAL' ? '#dc2626'
                                                         : (hr.severity||'').toUpperCase() === 'HIGH'     ? '#ea580c'
                                                         : (hr.severity||'').toUpperCase() === 'MEDIUM'   ? '#d97706'
                                                         : (hr.severity||'').toUpperCase() === 'LOW'      ? '#65a30d'
                                                         : '#6b7280',
                                                    border: '1px solid',
                                                    borderColor: (hr.severity||'').toUpperCase() === 'CRITICAL' ? 'rgba(220,38,38,0.25)'
                                                               : (hr.severity||'').toUpperCase() === 'HIGH'     ? 'rgba(234,88,12,0.25)'
                                                               : (hr.severity||'').toUpperCase() === 'MEDIUM'   ? 'rgba(217,119,6,0.25)'
                                                               : (hr.severity||'').toUpperCase() === 'LOW'      ? 'rgba(101,163,13,0.25)'
                                                               : 'rgba(107,114,128,0.25)'
                                                }"
                                            ></span>
                                        </td>
                                        <td style="text-align:center; white-space:nowrap;">
                                            <span
                                                x-text="hr.cvss > 0 ? parseFloat(hr.cvss).toFixed(1) : '-'"
                                                :style="{
                                                    fontWeight:'700', fontSize:'11px',
                                                    color: hr.cvss >= 9 ? '#dc2626'
                                                         : hr.cvss >= 7 ? '#ea580c'
                                                         : hr.cvss >= 4 ? '#d97706'
                                                         : hr.cvss >  0 ? '#65a30d'
                                                         : '#9ca3af'
                                                }"
                                            ></span>
                                        </td>
                                        <td style="max-width:180px; font-size:11px; white-space:normal; word-break:break-word; line-height:1.4;" x-text="hr.nvt || '-'"></td>
                                        <td style="font-size:10px; white-space:normal; line-height:1.6;">
                                            <template x-if="hr.cves">
                                                <div style="display:flex; flex-wrap:wrap; gap:2px;">
                                                    <template x-for="cve in hr.cves.split(/[\s,;]+/).filter(c => c.startsWith('CVE-'))" :key="cve">
                                                        <span class="cpm-cve-badge" x-text="cve"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!hr.cves">
                                                <span style="color:var(--cpm-text-xs);">-</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                            <template x-if="!hostPanelData?.rows || hostPanelData.rows.length === 0">
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:24px; color:var(--cpm-text-muted); font-size:12px;">
                                        No hay vulnerabilidades registradas para este host.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>
