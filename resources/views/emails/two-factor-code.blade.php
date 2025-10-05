<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Código de Verificación - {{ config('app.name') }}</title>
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
            border-bottom: 3px solid #3498db;
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
            color: #2c3e50;
            font-weight: 600;
            margin: 8px 0 0 0;
            letter-spacing: -0.3px;
        }

        .content {
            padding: 40px;
            background-color: #ffffff;
        }

        .greeting {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .intro-text {
            font-size: 15px;
            color: #34495e;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .code-box {
            background-color: #f8f9fa;
            border: 2px solid #3498db;
            border-radius: 4px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }

        .code-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .verification-code {
            font-size: 42px;
            color: #3498db;
            font-weight: 700;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
        }

        .code-hint {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 15px;
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
        }

        .notice-box {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 4px;
            padding: 20px;
            margin: 30px 0;
        }

        .notice-box p {
            font-size: 14px;
            color: #1565c0;
            margin: 0;
            line-height: 1.6;
        }

        .notice-box strong {
            color: #0d47a1;
        }

        .security-tips {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 20px;
            margin: 30px 0;
        }

        .security-tips .section-title {
            border-bottom: none;
            margin-bottom: 15px;
            padding-bottom: 0;
        }

        .security-tips ul {
            margin: 0;
            padding-left: 20px;
        }

        .security-tips li {
            font-size: 14px;
            color: #34495e;
            margin-bottom: 10px;
            line-height: 1.6;
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

            .info-row {
                display: block;
            }

            .info-label,
            .info-value {
                display: block;
                width: 100%;
                padding: 0;
            }

            .info-label {
                margin-bottom: 5px;
            }

            .verification-code {
                font-size: 32px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <img src="{{ $logoUrl }}" alt="Logo Municipalidad" class="logo">
            </div>
            <p class="header-title">Estimado/a {{ $userName ?? 'Usuario' }}</p>
            <h1 class="document-type">Código de Verificación</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="intro-text">
                Hemos recibido una solicitud para acceder a su cuenta. Para garantizar su seguridad, necesitamos verificar su identidad mediante el siguiente código.
            </p>

            <!-- Verification Code -->
            <div class="code-box">
                <div class="code-label">Su código de verificación</div>
                <div class="verification-code">{{ $verificationCode ?? ($code ?? '123456') }}</div>
                <div class="code-hint">Ingrese este código en la aplicación</div>
            </div>

            <!-- Información -->
            <div class="info-section">
                <div class="section-title">Información Importante</div>
                
                <div class="info-row">
                    <span class="info-label">Validez del código</span>
                    <span class="info-value">{{ $expiresInMinutes ?? 10 }} minutos</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Uso</span>
                    <span class="info-value">Único (no reutilizable)</span>
                </div>
            </div>

            <!-- Security Tips -->
            <div class="security-tips">
                <div class="section-title">Recomendaciones de Seguridad</div>
                <ul>
                    <li>Nunca comparta este código con terceros</li>
                    <li>Solo ingréselo en la aplicación oficial</li>
                    <li>Nuestro equipo nunca le solicitará códigos por teléfono o email</li>
                    <li>Si no reconoce esta solicitud, contacte inmediatamente a soporte</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-brand">Sistema Leykarin</p>
            <p class="footer-text">Ilustre Municipalidad de Arica</p>
            <p class="footer-text">Este es un correo automático, por favor no responda a este mensaje.</p>
            
            <div class="footer-legal">
                <p>&copy; {{ date('Y') }} Municipalidad de Arica. Todos los derechos reservados.</p>
                <p>Este documento es confidencial y está destinado únicamente para el uso del destinatario.</p>
            </div>
        </div>
    </div>
</body>

</html>
