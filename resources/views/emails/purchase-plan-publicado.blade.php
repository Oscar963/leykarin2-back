<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Plan de Compra Publicado</title>
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
            background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
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
            border-left: 4px solid #fd7e14;
        }

        h2 {
            color: #fd7e14;
            margin-top: 0;
            font-size: 24px;
            border-bottom: 2px solid #e6e9f0;
            padding-bottom: 10px;
        }

        h3 {
            color: #fd7e14;
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
            color: #fd7e14;
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
            color: #fd7e14;
            font-weight: bold;
        }

        .municipality-name {
            color: #e55a00;
            font-weight: bold;
        }

        .status-badge {
            background-color: #fd7e14;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }

        .final-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }

        .publication-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #fd7e14;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/img/logos/logo-blanco.png') }}" alt="Logo Municipalidad de Arica" class="logo">
        </div>

        <div class="content">
            <h2>Plan de Compra Publicado</h2>

            <p>Estimado/a usuario,</p>
            <p>El plan de compra <span class="highlight">{{ $purchasePlan->name }}</span> ha sido <strong>publicado oficialmente</strong> y ahora está disponible para consulta pública.</p>

            <div class="status-badge">Estado: Publicado</div>

            <div class="final-notice">
                <strong>¡Proceso Completado!</strong> El plan de compra ha sido publicado oficialmente y está disponible para consulta pública a través de los canales oficiales de la Municipalidad de Arica.
            </div>

            <div class="details">
                <h3>Detalles del Plan de Compra</h3>
                <p><strong>Año:</strong> {{ $purchasePlan->year }}</p>
                <p><strong>Dirección:</strong> {{ $purchasePlan->direction->name ?? 'N/A' }}</p>
                <p><strong>Monto F1:</strong> ${{ number_format($purchasePlan->formF1->amount ?? 0, 0, ',', '.') }}</p>
                <p><strong>Fecha de publicación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                <p><strong>Número de decreto:</strong> {{ $purchasePlan->decreto->name ?? 'N/A' }}</p>
                <p><strong>Estado final:</strong> Publicado oficialmente</p>
                @if($comment)
                <p><strong>Comentario:</strong> {{ $comment }}</p>
                @endif
            </div>

            <div class="publication-info">
                <h3>Información de Publicación</h3>
                <p><strong>Disponibilidad:</strong> El plan de compra está ahora disponible para consulta pública</p>
                <p><strong>Acceso:</strong> Puede ser consultado a través de los canales oficiales de la municipalidad</p>
                <p><strong>Vigencia:</strong> El decreto está en vigencia y es de cumplimiento obligatorio</p>
            </div>

            <p>El proceso de planificación de compras ha sido completado exitosamente. El plan de compra ahora es un documento oficial publicado y disponible para consulta pública.</p>

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