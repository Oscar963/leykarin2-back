<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Restablecer Contraseña - {{ config('app.name') }}</title>
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

        .intro-text {
            font-size: 15px;
            color: #34495e;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .alert-box {
            background-color: #fff8e1;
            border: 1px solid #ffecb3;
            border-left: 4px solid #ffa726;
            border-radius: 4px;
            padding: 20px;
            margin: 30px 0;
        }

        .alert-box strong {
            font-size: 14px;
            color: #e65100;
            display: block;
            margin-bottom: 8px;
        }

        .alert-box p {
            font-size: 14px;
            color: #ef6c00;
            margin: 0;
            line-height: 1.6;
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

        .step-number {
            display: inline-block;
            width: 24px;
            height: 24px;
            background-color: #3498db;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-size: 13px;
            font-weight: 600;
            margin-right: 10px;
        }

        .cta-container {
            text-align: center;
            margin: 40px 0;
        }

        .cta-button {
            display: inline-block;
            background-color: #3498db;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 40px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.3px;
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

        .link-text {
            font-size: 13px;
            color: #7f8c8d;
            margin: 20px 0 10px 0;
        }

        .link-url {
            word-break: break-all;
            color: #3498db;
            font-size: 12px;
            font-family: 'Courier New', monospace;
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid #ecf0f1;
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

            .cta-button {
                display: block;
                padding: 14px 30px;
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
            <p class="header-title">Solicitud de Restablecimiento</p>
            <h1 class="document-type">Restablecer Contraseña</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="intro-text">
                Hemos recibido una solicitud para restablecer la contraseña de su cuenta en el Sistema de Denuncias Leykarin.
            </p>

            <!-- Alert Box -->
            <div class="alert-box">
                <strong>Aviso de Seguridad</strong>
                <p>Si usted no solicitó este cambio de contraseña, por favor ignore este mensaje. Su cuenta permanecerá segura y no se realizará ningún cambio.</p>
            </div>

            <!-- Instrucciones -->
            <div class="info-section">
                <div class="section-title">Instrucciones para Restablecer</div>
                
                <div class="info-row">
                    <span class="info-label"><span class="step-number">1</span> Paso 1</span>
                    <span class="info-value">Haga clic en el botón "Restablecer Contraseña"</span>
                </div>

                <div class="info-row">
                    <span class="info-label"><span class="step-number">2</span> Paso 2</span>
                    <span class="info-value">Será dirigido a una página segura</span>
                </div>

                <div class="info-row">
                    <span class="info-label"><span class="step-number">3</span> Paso 3</span>
                    <span class="info-value">Ingrese su nueva contraseña y confírmela</span>
                </div>
            </div>

            <!-- Notice Box -->
            <div class="notice-box">
                <p><strong>Tiempo limitado:</strong> Este enlace expira en 60 minutos por su seguridad.</p>
            </div>

            <!-- CTA Button -->
            <div class="cta-container">
                <a href="{{ $resetUrl }}" class="cta-button">Restablecer Contraseña</a>
            </div>

            <p class="link-text">Si el botón no funciona, copie y pegue el siguiente enlace en su navegador:</p>
            <div class="link-url">{{ $resetUrl }}</div>
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
