<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Importación
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar todas las opciones relacionadas con la importación
    | de archivos Excel/CSV. Estas configuraciones pueden ser sobrescritas
    | en el archivo .env
    |
    */

    'defaults' => [
        'batch_size' => env('IMPORT_BATCH_SIZE', 1000),
        'chunk_size' => env('IMPORT_CHUNK_SIZE', 100),
        'timeout' => env('IMPORT_TIMEOUT', 300),
        'memory_limit' => env('IMPORT_MEMORY_LIMIT', '512M'),
    ],

    'validation' => [
        'strict_mode' => env('VALIDATION_STRICT_MODE', true),
        'skip_duplicates' => env('VALIDATION_SKIP_DUPLICATES', true),
        'max_errors' => env('VALIDATION_MAX_ERRORS', 10),
        'required_columns' => ['numero', 'descripcion'],
        'optional_columns' => [
            'calle',
            'numeracion',
            'lote_sitio',
            'manzana',
            'poblacion_villa',
            'foja',
            'inscripcion_numero',
            'inscripcion_anio',
            'rol_avaluo',
            'superficie',
            'deslinde_norte',
            'deslinde_sur',
            'deslinde_este',
            'deslinde_oeste',
            'decreto_incorporacion',
            'decreto_destinacion',
            'observaciones'
        ],
    ],

    'security' => [
        'max_attempts_per_hour' => env('IMPORT_MAX_ATTEMPTS_PER_HOUR', 999999),
        'decay_minutes' => env('IMPORT_DECAY_MINUTES', 1),
        'max_total_size_per_hour' => env('IMPORT_MAX_TOTAL_SIZE_PER_HOUR', 999999 * 1024 * 1024), // 999GB
        'max_concurrent_imports' => env('IMPORT_MAX_CONCURRENT_IMPORTS', 999),
        'concurrent_timeout' => env('IMPORT_CONCURRENT_TIMEOUT', 300), // 5 minutos
    ],

    'storage' => [
        'disk' => env('FILESYSTEM_DISK', 'public'),
        'path' => env('UPLOAD_PATH', 'uploads/compliants'),
        'backup_disk' => env('BACKUP_STORAGE', 'local'),
    ],

    'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'xlsx,xls,csv')),
    'max_file_size' => env('MAX_FILE_SIZE', 10240), // KB

    'preview' => [
        'rows' => env('IMPORT_PREVIEW_ROWS', 5),
    ],

    'logging' => [
        'enabled' => env('IMPORT_LOG_ENABLED', true),
        'level' => env('LOG_LEVEL', 'info'),
    ],

    'queue' => [
        'enabled' => env('IMPORT_QUEUE_ENABLED', false),
        'connection' => env('QUEUE_CONNECTION', 'sync'),
        'queue_name' => env('IMPORT_QUEUE_NAME', 'imports'),
        'retry_attempts' => env('IMPORT_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('IMPORT_RETRY_DELAY', 60),
    ],

    'notifications' => [
        'enabled' => env('IMPORT_NOTIFICATIONS_ENABLED', false),
        'email' => env('IMPORT_NOTIFICATION_EMAIL', null),
    ],
];
