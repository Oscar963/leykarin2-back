<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Plan de Compra Aprobado</title>
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
            width: 150px;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer p:last-child {
            color: #999;
        }
        .highlight {
            color: #2e7d32;
            font-weight: bold;
        }
        .municipality-name {
            color: #43a047;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/img/logos/logo-blanco.png') }}" alt="Logo Municipalidad de Arica" class="logo">
        </div>
        <div class="content">
            <h2>Plan de Compra Aprobado</h2>

            <p>Estimado/a usuario,</p>
            <p>El plan de compra <span class="highlight">{{ $purchasePlan->name }}</span> ha sido aprobado exitosamente.</p>

            <div class="details">
                <h3>Detalles del Plan de Compra</h3>
                <p><strong>Año:</strong> {{ $purchasePlan->year }}</p>
                <p><strong>Monto F1:</strong> ${{ number_format($purchasePlan->amount_F1, 0, ',', '.') }}</p>
                <p><strong>Fecha de creación:</strong> {{ $purchasePlan->created_at }}</p> 
                <p><strong>Estado actual:</strong> Aprobado</p>
                @if($comment)
                <p><strong>Comentario:</strong> {{ $comment }}</p>
                @endif
            </div>

            <p>Su plan de compra ha sido revisado y aprobado por las autoridades correspondientes. Puede proceder con los siguientes pasos del proceso.</p>

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