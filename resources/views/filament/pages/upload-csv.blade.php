<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" color="success" icon="heroicon-o-arrow-up-tray">
                Procesar CSV
            </x-filament::button>
            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\CsvReportResource::getUrl('index') }}"
                color="gray"
                class="ml-2"
            >
                Cancelar
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
