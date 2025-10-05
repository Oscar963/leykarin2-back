<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Alerta de Seguridad - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #ecf0f1;
            margin: 0;
            padding: 30px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .header {
            background-color: #ffffff;
            padding: 30px 40px 20px;
            border-bottom: 3px solid #e74c3c;
        }

        .logo-container {
            text-align: left;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 150px;
            height: auto;
        }

        .header-title {
            font-size: 13px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            font-weight: 500;
        }

        .document-type {
            font-size: 24px;
            color: #e74c3c;
            font-weight: 600;
            margin: 8px 0 0 0;
            letter-spacing: -0.3px;
        }

        .content {
            padding: 40px;
            background-color: #ffffff;
        }

        .intro-text {
            font-size: 15px;
            color: #34495e;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .alert-box {
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
            border-left: 4px solid #e74c3c;
            border-radius: 4px;
            padding: 25px;
            margin: 30px 0;
        }

        .alert-box .alert-title {
            font-size: 16px;
            color: #c62828;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .alert-detail {
            display: table;
            width: 100%;
            padding: 8px 0;
        }

        .alert-label {
            display: table-cell;
            font-size: 13px;
            color: #d32f2f;
            width: 30%;
            font-weight: 600;
        }

        .alert-value {
            display: table-cell;
            font-size: 13px;
            color: #c62828;
            font-family: 'Courier New', monospace;
        }

        .info-section {
            margin: 30px 0;
        }

        .section-title {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .info-row {
            display: table;
            width: 100%;
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            display: table-cell;
            font-size: 14px;
            color: #7f8c8d;
            width: 35%;
            padding-right: 15px;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            font-size: 14px;
            color: #2c3e50;
            font-weight: 500;
            word-break: break-all;
        }

        .action-box {
            background-color: #fff8e1;
            border: 1px solid #ffecb3;
            border-left: 4px solid #ffa726;
            border-radius: 4px;
            padding: 20px;
            margin: 30px 0;
        }

        .action-box strong {
            font-size: 14px;
            color: #e65100;
            display: block;
            margin-bottom: 12px;
        }

        .action-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .action-box li {
            font-size: 14px;
            color: #ef6c00;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .json-section {
            background-color: #f8f9fa;
            border: 1px solid #ecf0f1;
            border-radius: 4px;
            padding: 20px;
            margin: 30px 0;
        }

        .json-section .section-title {
            border-bottom: none;
            margin-bottom: 15px;
        }

        .json-section pre {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
            line-height: 1.5;
            font-family: 'Courier New', monospace;
        }

        .divider {
            height: 1px;
            background-color: #ecf0f1;
            margin: 30px 0;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 30px 40px;
            border-top: 1px solid #ecf0f1;
        }

        .footer-text {
            font-size: 12px;
            color: #7f8c8d;
            line-height: 1.6;
            margin: 8px 0;
        }

        .footer-brand {
            font-size: 13px;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .footer-legal {
            font-size: 11px;
            color: #95a5a6;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ecf0f1;
        }

        @media only screen and (max-width: 600px) {
            body {
                padding: 15px 0;
            }

            .email-container {
                margin: 0 10px;
            }

            .header,
            .content,
            .footer {
                padding: 25px 20px;
            }

            .info-row,
            .alert-detail {
                display: block;
            }

            .info-label,
            .info-value,
            .alert-label,
            .alert-value {
                display: block;
                width: 100%;
                padding: 0;
            }

            .info-label,
            .alert-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <img src="{{ asset('assets/img/logos/logo-blanco.png') }}" alt="Logo Municipalidad" class="logo">
            </div>
            <p class="header-title">Sistema de Monitoreo</p>
            <h1 class="document-type">Alerta de Seguridad</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="intro-text">
                Se ha detectado un evento de seguridad en el sistema que requiere su atención inmediata.
            </p>

            <!-- Alert Box -->
            <div class="alert-box">
                <div class="alert-title">Resumen del Evento</div>
                
                <div class="alert-detail">
                    <span class="alert-label">Evento:</span>
                    <span class="alert-value">{{ $event }}</span>
                </div>

                <div class="alert-detail">
                    <span class="alert-label">Timestamp:</span>
                    <span class="alert-value">{{ $timestamp }}</span>
                </div>

                <div class="alert-detail">
                    <span class="alert-label">Entorno:</span>
                    <span class="alert-value">{{ strtoupper($environment) }}</span>
                </div>
            </div>

            <!-- Información del Evento -->
            <div class="info-section">
                <div class="section-title">Detalles del Evento</div>
                
                @if(isset($data['user_id']))
                <div class="info-row">
                    <span class="info-label">Usuario ID</span>
                    <span class="info-value">{{ $data['user_id'] }}</span>
                </div>
                @endif
                
                @if(isset($data['user_email']))
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $data['user_email'] }}</span>
                </div>
                @endif
                
                @if(isset($data['ip_address']) || isset($data['current_ip']))
                <div class="info-row">
                    <span class="info-label">Dirección IP</span>
                    <span class="info-value">{{ $data['ip_address'] ?? $data['current_ip'] ?? 'N/A' }}</span>
                </div>
                @endif
                
                @if(isset($data['session_ip']))
                <div class="info-row">
                    <span class="info-label">IP de Sesión</span>
                    <span class="info-value">{{ $data['session_ip'] }}</span>
                </div>
                @endif
                
                @if(isset($data['url']))
                <div class="info-row">
                    <span class="info-label">URL</span>
                    <span class="info-value">{{ $data['url'] }}</span>
                </div>
                @endif
            </div>

            <!-- Action Required -->
            <div class="action-box">
                <strong>Acción Requerida</strong>
                <ul>
                    <li>Revise los logs de seguridad en storage/logs/security.log</li>
                    <li>Verifique la actividad del usuario afectado</li>
                    <li>Tome las medidas correctivas necesarias</li>
                    <li>Documente el incidente y las acciones tomadas</li>
                    <li>Notifique al equipo de seguridad si es necesario</li>
                </ul>
            </div>

            <!-- JSON Details -->
            <div class="json-section">
                <div class="section-title">Información Técnica Completa</div>
                <pre>{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-brand">Sistema Leykarin - Monitoreo de Seguridad</p>
            <p class="footer-text">Ilustre Municipalidad de Arica</p>
            <p class="footer-text">Este es un correo automático, por favor no responda a este mensaje.</p>
            
            <div class="footer-legal">
                <p>Generado el {{ now()->format('d/m/Y H:i:s') }} | Entorno: {{ config('app.env') }}</p>
                <p>&copy; {{ date('Y') }} Municipalidad de Arica. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>

</html>
