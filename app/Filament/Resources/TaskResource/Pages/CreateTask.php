<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\OpenvasTask;
use App\Services\OpenVasApiClient;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function handleRecordCreation(array $data): OpenvasTask
    {
        $api = app(OpenVasApiClient::class);

        // 1. Crear target
        $targetResult = $api->createTarget(
            name:       $data['target_name'],
            hosts:      $data['target_hosts'],
            portListId: $data['port_list_id'] ?? null,
            customPorts: $data['custom_ports'] ?? null,
        );
        $targetId = $targetResult['target_id'] ?? null;

        if (! $targetId) {
            Notification::make()->title('Error al crear el target en OpenVAS')->danger()->send();
            $this->halt();
        }

        // 2. Crear tarea
        $taskResult = $api->createTask(
            name:     $data['name'],
            configId: $data['config_id'],
            targetId: $targetId,
        );
        $gvmId = $taskResult['task_id'] ?? null;

        if (! $gvmId) {
            Notification::make()->title('Error al crear la tarea en OpenVAS')->danger()->send();
            $this->halt();
        }

        // 3. Guardar en DB local
        return OpenvasTask::create([
            'gvm_id'         => $gvmId,
            'name'           => $data['name'],
            'status'         => 'New',
            'progress'       => 0,
            'config_id'      => $data['config_id'],
            'target_id'      => $targetId,
            'last_synced_at' => now(),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
