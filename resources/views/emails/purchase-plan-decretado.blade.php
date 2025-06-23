<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Plan de Compra Decretado</title>
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
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
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
            border-left: 4px solid #6f42c1;
        }

        h2 {
            color: #6f42c1;
            margin-top: 0;
            font-size: 24px;
            border-bottom: 2px solid #e6e9f0;
            padding-bottom: 10px;
        }

        h3 {
            color: #6f42c1;
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
            color: #6f42c1;
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
            color: #6f42c1;
            font-weight: bold;
        }

        .municipality-name {
            color: #5a32a3;
            font-weight: bold;
        }

        .status-badge {
            background-color: #6f42c1;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }

        .success-notice {
            background-color: #e2d9f3;
            border: 1px solid #d1b3ff;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #4a2c5a;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/img/logos/logo-blanco.png') }}" alt="Logo Municipalidad de Arica" class="logo">
        </div>

        <div class="content">
            <h2>Plan de Compra Decretado</h2>

            <p>Estimado/a usuario,</p>
            <p>El plan de compra <span class="highlight">{{ $purchasePlan->name }}</span> ha sido <strong>decretado</strong> exitosamente y ahora es un documento oficial de la Municipalidad de Arica.</p>

            <div class="status-badge">Estado: Decretado</div>

            <div class="success-notice">
                <strong>¡Felicidades!</strong> El plan de compra ha sido oficialmente decretado y ahora tiene carácter de documento municipal. Se procederá con su publicación oficial.
            </div>

            <div class="details">
                <h3>Detalles del Plan de Compra</h3>
                <p><strong>Año:</strong> {{ $purchasePlan->year }}</p>
                <p><strong>Dirección:</strong> {{ $purchasePlan->direction->name ?? 'N/A' }}</p>
                <p><strong>Monto F1:</strong> ${{ number_format($purchasePlan->formF1->amount ?? 0, 0, ',', '.') }}</p>
                <p><strong>Fecha de decretación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                <p><strong>Número de decreto:</strong> {{ $purchasePlan->decreto->name ?? 'En proceso' }}</p>
                <p><strong>Próximo paso:</strong> Publicación oficial</p>
                @if($comment)
                <p><strong>Comentario:</strong> {{ $comment }}</p>
                @endif
            </div>

            <p>El plan de compra ha sido procesado como decreto municipal y ahora es un documento oficial con validez legal. Se encuentra listo para ser publicado oficialmente y será accesible a través de los canales oficiales de la municipalidad.</p>

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