<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Plan de Compra Aprobado para Decretar</title>
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
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
            border-left: 4px solid #17a2b8;
        }

        h2 {
            color: #17a2b8;
            margin-top: 0;
            font-size: 24px;
            border-bottom: 2px solid #e6e9f0;
            padding-bottom: 10px;
        }

        h3 {
            color: #17a2b8;
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
            color: #17a2b8;
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
            color: #17a2b8;
            font-weight: bold;
        }

        .municipality-name {
            color: #138496;
            font-weight: bold;
        }

        .status-badge {
            background-color: #17a2b8;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }

        .important-notice {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #0c5460;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/img/logos/logo-blanco.png') }}" alt="Logo Municipalidad de Arica" class="logo">
        </div>

        <div class="content">
            <h2>Plan de Compra Aprobado para Decretar</h2>

            <p>Estimado/a usuario,</p>
            <p>El plan de compra <span class="highlight">{{ $purchasePlan->name }}</span> ha sido <strong>aprobado para decretar</strong> y está listo para ser procesado como decreto municipal.</p>

            <div class="status-badge">Estado: Aprobado para Decretar</div>

            <div class="important-notice">
                <strong>Importante:</strong> El plan de compra ha sido completamente aprobado y autorizado para ser convertido en decreto municipal.
            </div>

            <div class="details">
                <h3>Detalles del Plan de Compra</h3>
                <p><strong>Año:</strong> {{ $purchasePlan->year }}</p>
                <p><strong>Dirección:</strong> {{ $purchasePlan->direction->name ?? 'N/A' }}</p>
                <p><strong>Monto F1:</strong> ${{ number_format($purchasePlan->formF1->amount ?? 0, 0, ',', '.') }}</p>
                <p><strong>Fecha de aprobación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                <p><strong>Próximo paso:</strong> Decretación</p>
                @if($comment)
                <p><strong>Comentario:</strong> {{ $comment }}</p>
                @endif
            </div>

            <p>El plan de compra ha cumplido con todos los requisitos de revisión y aprobación. Se encuentra autorizado para ser procesado como decreto municipal y será publicado oficialmente una vez completado el proceso de decretación.</p>

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