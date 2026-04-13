<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\OpenvasTask;
use App\Services\OpenVasApiClient;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Sincronizar TODAS las tareas desde OpenVAS
            Actions\Action::make('sync_all')
                ->label('Sincronizar desde OpenVAS')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () {
                    try {
                        $api   = app(OpenVasApiClient::class);
                        $tasks = $api->getTasks();
                        foreach ($tasks as $t) {
                            OpenvasTask::syncFromApi($t);
                        }
                        Notification::make()
                            ->title(count($tasks) . ' tareas sincronizadas')
                            ->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al sincronizar: ' . $e->getMessage())
                            ->danger()->send();
                    }
                }),

            Actions\CreateAction::make()
                ->label('Nueva Tarea'),
        ];
    }
}
