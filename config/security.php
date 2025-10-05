<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuración para el monitoreo de seguridad y alertas.
    |
    */

    'monitoring_enabled' => env('SECURITY_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Alert Emails
    |--------------------------------------------------------------------------
    |
    | Emails que recibirán alertas de seguridad críticas.
    | Puede ser una cadena separada por comas o un array.
    |
    */

    'alert_emails' => env('SECURITY_ALERT_EMAILS', env('SECURITY_ALERT_EMAIL', '')),

    /*
    |--------------------------------------------------------------------------
    | Slack Webhook
    |--------------------------------------------------------------------------
    |
    | Webhook de Slack para enviar alertas en tiempo real.
    |
    */

    'slack_webhook' => env('SECURITY_SLACK_WEBHOOK'),

    /*
    |--------------------------------------------------------------------------
    | Trusted IPs
    |--------------------------------------------------------------------------
    |
    | IPs confiables que pueden cambiar durante la sesión (proxies, VPN).
    |
    */

    'trusted_ips' => explode(',', env('TRUSTED_IPS', '')),

    /*
    |--------------------------------------------------------------------------
    | Allow Same Subnet
    |--------------------------------------------------------------------------
    |
    | Permitir cambio de IP si está en la misma subred /24.
    |
    */

    'allow_same_subnet' => env('ALLOW_SAME_SUBNET', true),

    /*
    |--------------------------------------------------------------------------
    | Security Audit
    |--------------------------------------------------------------------------
    |
    | Configuración para auditorías de seguridad automatizadas.
    |
    */

    'audit_enabled' => env('SECURITY_AUDIT_ENABLED', true),

];
