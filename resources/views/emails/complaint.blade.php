<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comprobante de Denuncia</title>
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
            background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%);
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
            border-left: 4px solid #2e7d32;
        }

        h2 {
            color: #2e7d32;
            margin-top: 0;
            font-size: 24px;
            border-bottom: 2px solid #e6e9f0;
            padding-bottom: 10px;
        }

        h3 {
            color: #2e7d32;
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
            color: #2e7d32;
            display: inline-block;
            width: 180px;
        }

        .status-badge {
            background-color: #2e7d32;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/img/logos/logo-blanco.png') }}" alt="Logo" class="logo">
        </div>
        <div class="content">
            <h2>Comprobante de Denuncia</h2>

            <p>Estimado/a {{ $complainant->name ?? 'usuario/a' }},</p>
            <p>Hemos <strong>recibido</strong> tu denuncia exitosamente. Conserva este comprobante para tu registro.</p>
            {{-- <div class="status-badge">Estado: Recibida</div> --}}

            <div class="details">
                <h3>Detalles de la Denuncia</h3>
                <p><strong>Folio:</strong> {{ $folio }}</p>
                <p><strong>Fecha de creación:</strong> {{ optional($createdAt)->format('d/m/Y H:i') }}</p>
                <p><strong>Denunciante:</strong> Nombre: {{ $complainant->name ?? 'N/A' }} RUT:
                    {{ $complainant->rut ?? 'N/A' }}</p>
                @if (!empty($complainant->email))
                    <p><strong>Correo de contacto:</strong> {{ $complainant->email }}</p>
                @endif
                <p><strong>Archivo adjunto:</strong> Comprobante completo en PDF</p>
                {{-- <p><strong>Próximo paso:</strong> Revisión por el equipo correspondiente</p> --}}
            </div>

            {{-- <p>Gracias por comunicarte con nosotros.</p> --}}
        </div>
        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            <p>&copy; {{ date('Y') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>

</html>
