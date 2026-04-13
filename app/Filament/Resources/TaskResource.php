<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\OpenvasTask;
use App\Services\OpenVasApiClient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = OpenvasTask::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Tareas de Escaneo';
    protected static ?string $navigationGroup = 'OpenVAS';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel      = 'Tarea';
    protected static ?string $pluralModelLabel = 'Tareas';

    // -------------------------------------------------------------------------
    // Form — Crear nueva tarea
    // -------------------------------------------------------------------------
    public static function form(Form $form): Form
    {
        $api = app(OpenVasApiClient::class);

        $configs = collect($api->getConfigs())
            ->pluck('name', 'id')
            ->toArray();

        $portLists = collect($api->getPortLists())
            ->pluck('name', 'id')
            ->toArray();

        return $form->schema([
            Forms\Components\Section::make('Nueva Tarea de Escaneo')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre de la Tarea')
                        ->required()
                        ->maxLength(120),

                    Forms\Components\Select::make('config_id')
                        ->label('Configuracion de Escaneo')
                        ->options($configs)
                        ->required()
                        ->searchable(),

                    Forms\Components\Section::make('Target (Objetivo)')
                        ->schema([
                            Forms\Components\TextInput::make('target_name')
                                ->label('Nombre del Target')
                                ->required(),

                            Forms\Components\TextInput::make('target_hosts')
                                ->label('Hosts / IPs')
                                ->placeholder('192.168.1.1, 192.168.1.0/24')
                                ->required(),

                            Forms\Components\Select::make('port_list_id')
                                ->label('Port List')
                                ->options($portLists)
                                ->searchable(),

                            Forms\Components\TextInput::make('custom_ports')
                                ->label('Puertos Personalizados (opcional)')
                                ->placeholder('80,443,8080'),
                        ]),
                ]),
        ]);
    }

    // -------------------------------------------------------------------------
    // Table
    // -------------------------------------------------------------------------
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'Done',
                        'info'    => 'Running',
                        'warning' => fn ($state) => in_array($state, ['Requested', 'Stopped']),
                        'danger'  => 'Interrupted',
                        'gray'    => 'New',
                    ]),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progreso')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_synced_at')
                    ->label('Ultima Sincronizacion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'New'         => 'New',
                        'Running'     => 'Running',
                        'Done'        => 'Done',
                        'Stopped'     => 'Stopped',
                        'Interrupted' => 'Interrupted',
                    ]),
            ])
            ->actions([
                // Sincronizar estado
                Tables\Actions\Action::make('sync')
                    ->label('Sincronizar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (OpenvasTask $record) {
                        try {
                            $api  = app(OpenVasApiClient::class);
                            $data = $api->getTask($record->gvm_id);
                            $record->update([
                                'status'         => $data['status'] ?? $record->status,
                                'progress'       => $data['progress'] ?? $record->progress,
                                'last_report_id' => $data['last_report_id'] ?? $record->last_report_id,
                                'last_synced_at' => now(),
                            ]);
                            Notification::make()->title('Tarea sincronizada')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error al sincronizar: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                // Iniciar tarea
                Tables\Actions\Action::make('start')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (OpenvasTask $r) => in_array($r->status, ['New', 'Stopped', 'Interrupted']))
                    ->requiresConfirmation()
                    ->action(function (OpenvasTask $record) {
                        try {
                            app(OpenVasApiClient::class)->startTask($record->gvm_id);
                            $record->update(['status' => 'Requested', 'last_synced_at' => now()]);
                            Notification::make()->title('Tarea iniciada')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                // Detener tarea
                Tables\Actions\Action::make('stop')
                    ->label('Detener')
                    ->icon('heroicon-o-stop')
                    ->color('warning')
                    ->visible(fn (OpenvasTask $r) => $r->status === 'Running')
                    ->requiresConfirmation()
                    ->action(function (OpenvasTask $record) {
                        try {
                            app(OpenVasApiClient::class)->stopTask($record->gvm_id);
                            $record->update(['status' => 'Stopped', 'last_synced_at' => now()]);
                            Notification::make()->title('Tarea detenida')->warning()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                // Reanudar
                Tables\Actions\Action::make('resume')
                    ->label('Reanudar')
                    ->icon('heroicon-o-play-pause')
                    ->color('info')
                    ->visible(fn (OpenvasTask $r) => $r->status === 'Stopped')
                    ->action(function (OpenvasTask $record) {
                        try {
                            app(OpenVasApiClient::class)->resumeTask($record->gvm_id);
                            $record->update(['status' => 'Requested', 'last_synced_at' => now()]);
                            Notification::make()->title('Tarea reanudada')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\ViewAction::make(),

                // Eliminar tarea
                Tables\Actions\DeleteAction::make()
                    ->action(function (OpenvasTask $record) {
                        try {
                            app(OpenVasApiClient::class)->deleteTask($record->gvm_id);
                            $record->delete();
                            Notification::make()->title('Tarea eliminada')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('sync_all')
                    ->label('Sincronizar Seleccionadas')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function ($records) {
                        $api = app(OpenVasApiClient::class);
                        foreach ($records as $record) {
                            try {
                                $data = $api->getTask($record->gvm_id);
                                $record->update([
                                    'status'         => $data['status'] ?? $record->status,
                                    'progress'       => $data['progress'] ?? $record->progress,
                                    'last_report_id' => $data['last_report_id'] ?? $record->last_report_id,
                                    'last_synced_at' => now(),
                                ]);
                            } catch (\Throwable) {}
                        }
                        Notification::make()->title('Tareas sincronizadas')->success()->send();
                    }),
            ])
            ->emptyStateHeading('No hay tareas registradas')
            ->emptyStateDescription('Sincroniza desde OpenVAS o crea una nueva tarea.')
            ->defaultSort('last_synced_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view'   => Pages\ViewTask::route('/{record}'),
        ];
    }
}
