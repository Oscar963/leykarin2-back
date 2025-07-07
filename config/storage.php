<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración personalizada para almacenamiento de archivos
    | usando variables de entorno para mayor flexibilidad
    |
    */

    'paths' => [
        'decretos' => env('STORAGE_PATH_DECRETOS', 'decretos'),
        'verifications' => env('STORAGE_PATH_VERIFICATIONS', 'verifications'),
        'templates' => env('STORAGE_PATH_TEMPLATES', 'templates'),
        'exports' => env('STORAGE_PATH_EXPORTS', 'exports'),
        'uploads' => env('STORAGE_PATH_UPLOADS', 'uploads'),
    ],

    'disks' => [
        'public' => env('FILESYSTEM_DISK_PUBLIC', 'public'),
        'private' => env('FILESYSTEM_DISK_PRIVATE', 'local'),
    ],

    'max_sizes' => [
        'pdf' => env('MAX_FILE_SIZE_PDF', 20480), // 20MB
        'image' => env('MAX_FILE_SIZE_IMAGE', 5120), // 5MB
        'document' => env('MAX_FILE_SIZE_DOCUMENT', 10240), // 10MB
        'spreadsheet' => env('MAX_FILE_SIZE_SPREADSHEET', 15360), // 15MB
    ],

    'allowed_extensions' => [
        'pdf' => explode(',', env('ALLOWED_FILE_EXTENSIONS_PDF', 'pdf')),
        'image' => explode(',', env('ALLOWED_FILE_EXTENSIONS_IMAGE', 'jpg,jpeg,png,gif')),
        'document' => explode(',', env('ALLOWED_FILE_EXTENSIONS_DOCUMENT', 'doc,docx,pdf')),
        'spreadsheet' => explode(',', env('ALLOWED_FILE_EXTENSIONS_SPREADSHEET', 'xls,xlsx,csv')),
    ],

    'retention' => [
        'audit_logs' => env('AUDIT_LOG_RETENTION_DAYS', 90),
        'security_logs' => env('SECURITY_LOG_RETENTION_DAYS', 365),
        'file_uploads' => env('FILE_UPLOAD_RETENTION_DAYS', 30),
    ],

]; 