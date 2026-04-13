<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Conectividad con la MV de OpenVAS</x-slot>
        <x-slot name="headerEnd">
            <x-filament::button wire:click="checkConnectivity" size="sm" color="info" icon="heroicon-o-arrow-path">
                Verificar
            </x-filament::button>
        </x-slot>

        @if (!$checked)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Haz clic en "Verificar" para comprobar la conexion con la MV.
            </p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                {{-- Estado general --}}
                <div class="flex items-center gap-3 p-3 rounded-lg {{ $connected ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                    @if ($connected)
                        <x-heroicon-o-check-circle class="h-8 w-8 text-green-500" />
                        <div>
                            <p class="font-semibold text-green-700 dark:text-green-400">Conectado</p>
                            <p class="text-xs text-gray-500">{{ $vmInfo }}</p>
                        </div>
                    @else
                        <x-heroicon-o-x-circle class="h-8 w-8 text-red-500" />
                        <div>
                            <p class="font-semibold text-red-700 dark:text-red-400">Sin conexion</p>
                            <p class="text-xs text-gray-500">{{ $errorMessage }}</p>
                        </div>
                    @endif
                </div>

                {{-- TCP / Latencia --}}
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <x-heroicon-o-signal class="h-8 w-8 text-blue-500" />
                    <div>
                        <p class="font-semibold text-gray-700 dark:text-gray-300">TCP Puerto 9390</p>
                        @if ($latencyMs !== null)
                            <p class="text-xs text-gray-500">Latencia: {{ $latencyMs }} ms</p>
                        @else
                            <p class="text-xs text-red-500">No alcanzable</p>
                        @endif
                    </div>
                </div>

                {{-- GMP Auth --}}
                <div class="flex items-center gap-3 p-3 rounded-lg {{ $authenticated ? 'bg-green-50 dark:bg-green-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20' }}">
                    <x-heroicon-o-lock-closed class="h-8 w-8 {{ $authenticated ? 'text-green-500' : 'text-yellow-500' }}" />
                    <div>
                        <p class="font-semibold {{ $authenticated ? 'text-green-700 dark:text-green-400' : 'text-yellow-700 dark:text-yellow-400' }}">
                            GMP Auth
                        </p>
                        @if ($openvasVersion)
                            <p class="text-xs text-gray-500">OpenVAS {{ $openvasVersion }}</p>
                        @elseif ($errorMessage)
                            <p class="text-xs text-red-500">{{ $errorMessage }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
