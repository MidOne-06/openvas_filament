<table style="width:100%;border-collapse:collapse;">
    <thead>
        <tr style="background:var(--df-row);">
            <th style="text-align:left;padding:9px 12px;font-size:11px;font-weight:700;color:var(--df-muted);text-transform:uppercase;">IP</th>
            <th style="text-align:center;padding:9px 12px;font-size:11px;font-weight:700;color:var(--df-muted);text-transform:uppercase;">Puerto</th>
            <th style="text-align:center;padding:9px 12px;font-size:11px;font-weight:700;color:var(--df-muted);text-transform:uppercase;">CVSS</th>
            <th style="text-align:center;padding:9px 12px;font-size:11px;font-weight:700;color:var(--df-muted);text-transform:uppercase;">Severidad</th>
            <th style="text-align:left;padding:9px 12px;font-size:11px;font-weight:700;color:var(--df-muted);text-transform:uppercase;">Vulnerabilidad</th>
        </tr>
    </thead>
    <tbody>
    @foreach(collect($rows)->take(50) as $row)
        @php
            $sv = strtoupper($row['severity'] ?? $row['Severity'] ?? '');
            $svColor = match($sv) {
                'CRITICAL' => '#dc2626', 'HIGH' => '#ea580c',
                'MEDIUM'   => '#d97706', 'LOW'  => '#16a34a',
                default    => '#6b7280'
            };
            $cvss = $row['cvss'] ?? $row['CVSS'] ?? 0;
        @endphp
        <tr style="border-top:1px solid var(--df-border);">
            <td style="padding:9px 12px;font-family:monospace;font-size:12px;color:var(--df-text);">{{ $row['ip'] ?? $row['IP'] ?? '-' }}</td>
            <td style="padding:9px 12px;text-align:center;font-size:12px;color:var(--df-muted);">{{ $row['port'] ?? $row['Port'] ?? '-' }}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:700;font-size:12px;color:{{ $cvss >= 7 ? '#dc2626' : ($cvss >= 4 ? '#d97706' : '#6b7280') }};">{{ $cvss }}</td>
            <td style="padding:9px 12px;text-align:center;">
                <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;color:{{ $svColor }};background:{{ $svColor }}1a;border:1px solid {{ $svColor }}33;">
                    {{ $sv ?: '-' }}
                </span>
            </td>
            <td style="padding:9px 12px;font-size:12px;color:var(--df-text);word-break:break-word;">{{ Str::limit($row['nvt_name'] ?? $row['NVT Name'] ?? '-', 80) }}</td>
        </tr>
    @endforeach
    @if(count($rows) > 50)
    <tr style="border-top:1px solid var(--df-border);">
        <td colspan="5" style="padding:10px 12px;text-align:center;font-size:12px;color:var(--df-muted);">
            ... y {{ count($rows) - 50 }} mas. Descarga el PDF o Excel para ver el listado completo.
        </td>
    </tr>
    @endif
    </tbody>
</table>
