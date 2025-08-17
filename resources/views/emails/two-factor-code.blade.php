<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Código de Verificación - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Manrope:wght@400;500;600&display=swap"
        rel="stylesheet">
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #475569;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .header {
            /* Cambiado gradiente verde por azul #06048c y variaciones */
            background: linear-gradient(135deg, #06048c 0%, #0f0b9e 100%);
            padding: 48px 40px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            opacity: 0.3;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .logo {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            font-weight: 700;
            font-family: 'Geist', sans-serif;
        }

        .header h1 {
            font-family: 'Geist', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header h2 {
            font-size: 18px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
        }

        .content {
            padding: 48px 40px;
            background-color: #ffffff;
        }

        .greeting {
            font-family: 'Geist', sans-serif;
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 24px;
        }

        .message {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 32px;
            line-height: 1.7;
        }

        .code-container {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            /* Cambiado borde verde por azul #06048c */
            border: 2px solid #06048c;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            /* Cambiado box-shadow verde por azul */
            box-shadow: 0 4px 6px -1px rgba(6, 4, 140, 0.1);
        }

        .code-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            /* Cambiado gradiente verde por variaciones de azul */
            background: linear-gradient(135deg, #06048c, #0f0b9e, #1e40af);
            border-radius: 20px;
            z-index: -1;
        }

        .code-label {
            font-family: 'Geist', sans-serif;
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .verification-code {
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, monospace;
            font-size: 48px;
            font-weight: 800;
            /* Cambiado color verde por azul más oscuro */
            color: #0f0b9e;
            letter-spacing: 16px;
            margin: 16px 0;
            /* Cambiado text-shadow verde por azul */
            text-shadow: 0 2px 4px rgba(15, 11, 158, 0.2);
            /* Cambiado gradiente verde por azul */
            background: linear-gradient(135deg, #06048c, #0f0b9e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .copy-hint {
            font-size: 13px;
            color: #94a3b8;
            font-style: italic;
            margin-top: 12px;
        }

        .info-cards {
            display: grid;
            gap: 16px;
        }

        .info-card {
            border-radius: 12px;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #06048c;
            margin-top: 16px;
        }

        .info-card-content h3 {
            font-family: 'Geist', sans-serif;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e293b;
        }

        .info-card-content p {
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            color: #64748b;
        }

        .security-tips {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #06048c;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }

        .security-tips h3 {
            font-family: 'Geist', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: #06048c;
            margin-bottom: 16px;
        }

        .security-tips ul {
            margin: 0;
            padding-left: 20px;
            color: #64748b;
        }

        .security-tips li {
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.6;
        }

        .cta-button {
            display: inline-block;
            /* Cambiado gradiente verde por azul */
            background: linear-gradient(135deg, #06048c 0%, #0f0b9e 100%);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-family: 'Geist', sans-serif;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 24px 0;
            /* Cambiado box-shadow verde por azul */
            box-shadow: 0 4px 6px -1px rgba(6, 4, 140, 0.3);
            transition: all 0.2s ease;
        }

        .signature {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 2px solid #f1f5f9;
        }

        .signature p {
            margin: 8px 0;
            color: #64748b;
        }

        .signature .team-name {
            font-family: 'Geist', sans-serif;
            font-weight: 600;
            color: #1e293b;
        }

        .footer {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 32px 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            font-size: 13px;
            color: #94a3b8;
            margin: 6px 0;
        }

        .footer .company-info {
            font-family: 'Geist', sans-serif;
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
        }

        .footer a {
            /* Cambiado color verde por azul */
            color: #06048c;
            text-decoration: none;
        }

        /* Responsive design */
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px 0;
            }

            .email-container {
                margin: 0 10px;
                border-radius: 12px;
            }

            .header,
            .content,
            .footer {
                padding: 32px 24px;
            }

            .header h1 {
                font-size: 28px;
            }

            .greeting {
                font-size: 20px;
            }

            .verification-code {
                font-size: 40px;
                letter-spacing: 12px;
            }

            .code-container {
                padding: 32px 20px;
                margin: 24px 0;
            }

            .info-cards {
                gap: 12px;
            }

            .info-card {
                padding: 16px;
                gap: 12px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #0f172a;
            }

            .email-container {
                background-color: #1e293b;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            }

            .content {
                background-color: #1e293b;
            }

            .greeting {
                color: #f1f5f9;
            }

            .message {
                color: #cbd5e1;
            }

            .code-container {
                background: linear-gradient(135deg, #334155 0%, #475569 100%);
                border-color: #06048c;
            }

            .verification-code {
                color: #60a5fa;
                background: linear-gradient(135deg, #3b82f6, #60a5fa);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .code-label {
                color: #cbd5e1;
            }

            .copy-hint {
                color: #94a3b8;
            }

            .info-card {
                background-color: #334155;
                border-color: #475569;
                border-left-color: #3b82f6;
            }

            .info-card-content h3 {
                color: #f1f5f9;
            }

            .info-card-content p {
                color: #cbd5e1;
            }

            .security-tips {
                background-color: #334155;
                border-color: #475569;
                border-left-color: #3b82f6;
            }

            .security-tips h3 {
                color: #60a5fa;
            }

            .security-tips li {
                color: #cbd5e1;
            }

            .signature p {
                color: #cbd5e1;
            }

            .signature .team-name {
                color: #f1f5f9;
            }

            .footer {
                background: linear-gradient(135deg, #334155 0%, #475569 100%);
                border-top-color: #475569;
            }

            .footer p {
                color: #94a3b8;
            }

            .footer .company-info {
                color: #cbd5e1;
            }

            .footer a {
                color: #60a5fa;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="logo">
                    <img src="{{ $logoUrl }}" alt="Logo Municipalidad de Arica" class="logo">
                    {{-- {{ substr(config('app.name', 'App'), 0, 1) }} --}}
                </div>
                <h1 style="color: #fff">{{ config('app.name', 'Mi Aplicación') }}</h1>
                <h2 style="color: #fff">Verificación de Identidad</h2>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="greeting">
                ¡Hola {{ $userName ?? 'Usuario' }}! 👋
            </div>

            <div class="message">
                <p>Hemos recibido una solicitud para acceder a tu cuenta. Para garantizar tu seguridad, necesitamos
                    verificar que realmente eres tú quien está intentando iniciar sesión.</p>
            </div>

            <!-- Verification Code -->
            <div class="code-container">
                <div class="code-label">Tu código de verificación</div>
                <div class="verification-code">{{ $verificationCode ?? ($code ?? '123456') }}</div>
                <div class="copy-hint">Copia y pega este código en la aplicación</div>
            </div>

            <!-- Information Cards -->
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-content">
                        <h3>Tiempo limitado</h3>
                        <p>Este código expira en {{ $expiresInMinutes ?? 10 }} minutos por tu seguridad.</p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-content">
                        <h3>¿No fuiste tú?</h3>
                        <p>Si no solicitaste este código, ignora este correo y contacta inmediatamente a nuestro equipo
                            de soporte.</p>
                    </div>
                </div>
            </div>

            <!-- Security Tips -->
            <div class="security-tips">
                <h3>Consejos de seguridad</h3>
                <ul>
                    <li>Nunca compartas este código con nadie, ni siquiera con nuestro equipo</li>
                    <li>Solo ingresa el código en la aplicación oficial de {{ config('app.name') }}</li>
                    <li>Nuestro equipo nunca te pedirá códigos por teléfono o email</li>
                </ul>
            </div>

            @if (isset($supportUrl))
                <a href="{{ $supportUrl }}" class="cta-button">
                    ¿Necesitas ayuda? Contactar Soporte
                </a>
            @endif

            <!-- Signature -->
            <div class="signature">
                <p class="team-name">El equipo de {{ config('app.name', 'Mi Aplicación') }}</p>
                <p>Comprometidos con tu seguridad digital</p>
                @if (isset($supportEmail))
                    <p style="font-size: 14px; margin-top: 16px;">
                        📧 Soporte: <a href="mailto:{{ $supportEmail }}"
                            style="color: #06048c; text-decoration: none;">{{ $supportEmail }}</a>
                    </p>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="company-info">{{ config('app.name', 'Mi Aplicación') }}</p>
            <p>Este es un correo automático generado por nuestro sistema de seguridad.</p>
            <p>© {{ date('Y') }} {{ config('app.name', 'Mi Aplicación') }}. Todos los derechos reservados.</p>
            @if (isset($unsubscribeUrl))
                <p style="margin-top: 16px;">
                    <a href="{{ $unsubscribeUrl }}" style="color: #94a3b8; font-size: 12px;">Gestionar preferencias de
                        email</a>
                </p>
            @endif
        </div>
    </div>
</body>

</html>
