<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Two Factor Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para autenticación de dos factores por email.
    |
    */

    // Tiempo de expiración del código en minutos
    'code_expires_minutes' => 10,

    // Longitud del código de verificación
    'code_length' => 6,

    // Rate limiting para códigos 2FA (intentos por minuto)
    'rate_limit_attempts' => 5,

    // Configuración de email
    'mail' => [
        'from_name' => env('MAIL_FROM_NAME', 'Sistema de Bienes Inmuebles'),
        'subject' => 'Código de Verificación - ' . env('APP_NAME'),
    ],
];
