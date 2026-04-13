<x-filament-panels::page>
<style>
:root {
    --df-bg:#ffffff; --df-border:#e5e7eb; --df-text:#111827;
    --df-muted:#6b7280; --df-row:#f9fafb; --df-input-bg:#ffffff;
    --df-input-border:#d1d5db;
}
.dark {
    --df-bg:#1f2937; --df-border:#374151; --df-text:#f9fafb;
    --df-muted:#9ca3af; --df-row:#111827; --df-input-bg:#1f2937;
    --df-input-border:#4b5563;
}
.df-card  { background:var(--df-bg);border:1px solid var(--df-border);border-radius:12px;padding:20px;margin-bottom:16px; }
.df-input { width:100%;background:var(--df-input-bg);border:1px solid var(--df-input-border);border-radius:8px;padding:9px 14px;color:var(--df-text);font-size:13px;box-sizing:border-box;font-family:monospace; }
.df-label { font-size:12px;font-weight:600;color:var(--df-muted);margin-bottom:5px;display:block; }
.df-btn   { border:none;border-radius:8px;padding:9px 20px;font-size:13px;font-weight:600;cursor:pointer; }
.df-badge { display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700; }
</style>

{{-- Formulario de comparacion --}}
<div class="df-card">
    <p style="font-size:14px;font-weight:700;color:var(--df-text);margin:0 0 16px;">Selecciona dos reportes para comparar</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:end;">
        <div>
            <label class="df-label">ID de Reporte A (base)</label>
            <input class="df-input" wire:model="report_id_a" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
        </div>
        <div>
            <label class="df-label">ID de Reporte B (comparar contra A)</label>
            <input class="df-input" wire:model="report_id_b" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
        </div>
    </div>
    <div style="display:flex;gap:10px;margin-top:16px;">
        <button class="df-btn" style="background:#6366f1;color:#fff;" wire:click="compare">
            Comparar Reportes
        </button>
        @if($compared)
        <a href="{{ $diffPdfUrl }}" target="_blank" class="df-btn" style="background:#dc2626;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            PDF del Diff
        </a>
        <a href="{{ $diffExcelUrl }}" target="_blank" class="df-btn" style="background:#059669;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            Excel del Diff
        </a>
        <button class="df-btn" style="background:var(--df-row);color:var(--df-text);" wire:click="clearDiff">Limpiar</button>
        @endif
    </div>
</div>

{{-- Resultados --}}
@if($compared && count($result) > 0)
@php
    $newVulns        = $result['new_vulnerabilities']        ?? [];
    $resolvedVulns   = $result['resolved_vulnerabilities']   ?? [];
    $persistentVulns = $result['persistent_vulnerabilities'] ?? [];
    $summary         = $result['summary']                    ?? [];
@endphp

{{-- Resumen de cambios --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:16px;text-align:center;">
        <div style="font-size:36px;font-weight:800;color:#dc2626;line-height:1;">{{ count($newVulns) }}</div>
        <div style="font-size:11px;font-weight:700;color:#b91c1c;text-transform:uppercase;letter-spacing:0.08em;margin-top:6px;">Nuevas Vulnerabilidades</div>
    </div>
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px;text-align:center;">
        <div style="font-size:36px;font-weight:800;color:#16a34a;line-height:1;">{{ count($resolvedVulns) }}</div>
        <div style="font-size:11px;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:0.08em;margin-top:6px;">Resueltas / Corregidas</div>
    </div>
    <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:16px;text-align:center;">
        <div style="font-size:36px;font-weight:800;color:#d97706;line-height:1;">{{ count($persistentVulns) }}</div>
        <div style="font-size:11px;font-weight:700;color:#b45309;text-transform:uppercase;letter-spacing:0.08em;margin-top:6px;">Persistentes</div>
    </div>
</div>

{{-- Tabla: Nuevas --}}
@if(count($newVulns) > 0)
<div class="df-card" style="padding:0;overflow:hidden;border-color:#fca5a5;">
    <div style="padding:12px 16px;background:#fef2f2;border-bottom:1px solid #fca5a5;">
        <span style="font-size:13px;font-weight:700;color:#dc2626;">Vulnerabilidades Nuevas ({{ count($newVulns) }})</span>
    </div>
    <div style="overflow-x:auto;">
        @include('filament.pages._diff_table', ['rows' => $newVulns])
    </div>
</div>
@endif

{{-- Tabla: Resueltas --}}
@if(count($resolvedVulns) > 0)
<div class="df-card" style="padding:0;overflow:hidden;border-color:#86efac;">
    <div style="padding:12px 16px;background:#f0fdf4;border-bottom:1px solid #86efac;">
        <span style="font-size:13px;font-weight:700;color:#16a34a;">Vulnerabilidades Resueltas ({{ count($resolvedVulns) }})</span>
    </div>
    <div style="overflow-x:auto;">
        @include('filament.pages._diff_table', ['rows' => $resolvedVulns])
    </div>
</div>
@endif

{{-- Tabla: Persistentes --}}
@if(count($persistentVulns) > 0)
<div class="df-card" style="padding:0;overflow:hidden;border-color:#fcd34d;">
    <div style="padding:12px 16px;background:#fffbeb;border-bottom:1px solid #fcd34d;">
        <span style="font-size:13px;font-weight:700;color:#d97706;">Vulnerabilidades Persistentes ({{ count($persistentVulns) }})</span>
    </div>
    <div style="overflow-x:auto;">
        @include('filament.pages._diff_table', ['rows' => $persistentVulns])
    </div>
</div>
@endif

@elseif($compared)
<div class="df-card" style="text-align:center;padding:40px;">
    <p style="color:var(--df-muted);font-size:13px;margin:0;">No se obtuvieron resultados. Verifica que ambos IDs de reporte sean validos.</p>
</div>
@endif

</x-filament-panels::page>
