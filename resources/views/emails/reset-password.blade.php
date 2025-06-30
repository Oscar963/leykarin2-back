<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Restablecer Contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 0;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding: 30px 0;
            background: linear-gradient(135deg, #0043b0 0%, #0057e6 100%);
            border-radius: 8px 8px 0 0;
        }

        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 10px;
        }

        .content {
            padding: 30px;
            background-color: #ffffff;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
            border-radius: 0 0 8px 8px;
        }

        .details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            border-left: 4px solid #0043b0;
        }

        h2 {
            color: #0043b0;
            margin-top: 0;
            font-size: 24px;
            border-bottom: 2px solid #e6e9f0;
            padding-bottom: 10px;
        }

        h3 {
            color: #0043b0;
            font-size: 18px;
            margin: 0 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #e6e9f0;
        }

        .details p {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e6e9f0;
        }

        .details p:last-child {
            border-bottom: none;
        }

        .details strong {
            color: #0043b0;
            display: inline-block;
            width: 150px;
        }

        .footer p {
            margin: 5px 0;
        }

        .footer p:last-child {
            color: #999;
        }

        .highlight {
            color: #0043b0;
            font-weight: bold;
        }

        .municipality-name {
            color: #0057e6;
            font-weight: bold;
        }

        .button {
            display: inline-block;
            background-color: #0043b0;
            color: white !important;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            border: none;
        }

        .button:hover {
            background-color: #00308d;
        }

        .security-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ $logoUrl }}" alt="Logo Municipalidad de Arica" class="logo">
        </div>

        <div class="content">
            <h2>Restablecer Contraseña</h2>

            <p>Estimado/a usuario,</p>
            <p>Hemos recibido una solicitud para <span class="highlight">restablecer tu contraseña</span> en el Sistema de Planificación de Compras.</p>

            <div class="security-notice">
                <strong>Importante:</strong> Si no fuiste tú quien solicitó el cambio de contraseña, por favor ignora este mensaje. Tu cuenta permanecerá segura.
            </div>

            <div class="details">
                <h3>Instrucciones para Restablecer</h3>
                <p><strong>Paso 1:</strong> Haz clic en el botón "Restablecer Contraseña"</p>
                <p><strong>Paso 2:</strong> Serás dirigido a una página segura</p>
                <p><strong>Paso 3:</strong> Ingresa tu nueva contraseña</p>
                <p><strong>Validez:</strong> Este enlace expira en 60 minutos</p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" class="button">Restablecer Contraseña</a>
            </div>

            <p>Si el botón no funciona, puedes copiar y pegar el siguiente enlace en tu navegador:</p>
            <p style="word-break: break-all; color: #666; font-size: 14px;">{{ $resetUrl }}</p>

            <p>Gracias por utilizar nuestro sistema de Planificación de Compras.</p>
            <p>Atentamente,<br>
                <strong>Sistema de Planificación de Compras | Ilustre Municipalidad de Arica</strong><br>
            </p>
        </div>

        <div class="footer">
            <p>Este es un correo automático, por favor no responda a este mensaje.</p>
            <p>&copy; {{ date('Y') }} Municipalidad de Arica. Todos los derechos reservados.</p>
        </div>
    </div>
</body>

</html>
