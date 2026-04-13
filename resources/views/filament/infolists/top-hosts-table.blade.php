@php
    $hosts = $getState() ?? [];
@endphp

@if(count($hosts) > 0)
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Host / IP</th>
                <th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Vulnerabilidades</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hosts as $i => $host)
            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <td class="px-3 py-2 text-gray-400">{{ $i+1 }}</td>
                <td class="px-3 py-2 font-mono text-sm font-medium">{{ $host['value'] }}</td>
                <td class="px-3 py-2 text-right">
                    <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:text-blue-300">
                        {{ $host['count'] }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<p class="text-sm text-gray-400">No hay datos de hosts disponibles. Presiona "Recargar KPIs".</p>
@endif
