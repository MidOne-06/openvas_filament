<?php

return [
    /*
    |--------------------------------------------------------------------------
    | URL base de la API OpenVAS (FastAPI backend)
    |--------------------------------------------------------------------------
    */
    'api_url' => env('OPENVAS_API_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Timeout en segundos para las peticiones HTTP
    |--------------------------------------------------------------------------
    */
    'timeout'  => (int) env('OPENVAS_API_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Mapeo de severidad → colores Filament
    |--------------------------------------------------------------------------
    */
    'severity_colors' => [
        'critical' => 'danger',
        'high'     => 'warning',
        'medium'   => 'info',
        'low'      => 'success',
        'info'     => 'gray',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo de estado de tarea → colores de badge
    |--------------------------------------------------------------------------
    */
    'task_status_colors' => [
        'Done'        => 'success',
        'Running'     => 'info',
        'Requested'   => 'warning',
        'Stopped'     => 'warning',
        'Interrupted' => 'danger',
        'New'         => 'gray',
    ],
];
