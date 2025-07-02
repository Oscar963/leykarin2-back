<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decreto Eliminado - Plan de Compra Revertido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .title {
            color: #d32f2f;
            font-size: 24px;
            margin: 20px 0;
        }
        .status-badge {
            display: inline-block;
            background-color: #ff9800;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .warning-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .details {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .details h3 {
            margin-top: 0;
            color: #495057;
        }
        .details p {
            margin: 8px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 12px;
        }
        .highlight {
            color: #d32f2f;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/img/logos/logo-azul.png') }}" alt="Municipalidad de Arica" class="logo">
            <h1 class="title">Decreto Eliminado</h1>
        </div>

        <p>Se ha eliminado un decreto asociado a un plan de compra. El plan de compra ha sido revertido automáticamente a su estado anterior.</p>

        <div class="status-badge">Estado: Aprobado para decretar</div>

        <div class="warning-notice">
            <strong>⚠️ Importante:</strong> El decreto ha sido eliminado del sistema. El plan de compra ha sido revertido automáticamente al estado "Aprobado para decretar" y requiere un nuevo decreto para continuar con el proceso.
        </div>

        <div class="details">
            <h3>Detalles del Plan de Compra</h3>
            <p><strong>Año:</strong> {{ $purchasePlan->year }}</p>
            <p><strong>Dirección:</strong> {{ $purchasePlan->direction->name ?? 'N/A' }}</p>
            <p><strong>Monto F1:</strong> ${{ number_format($purchasePlan->formF1->amount ?? 0, 0, ',', '.') }}</p>
            <p><strong>Fecha de eliminación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            <p><strong>Estado actual:</strong> Aprobado para decretar</p>
            <p><strong>Acción requerida:</strong> Subir nuevo decreto</p>
        </div>

        <p>El plan de compra ha sido revertido al estado "Aprobado para decretar" y está listo para recibir un nuevo decreto. Se debe proceder con la subida de un nuevo documento de decreto para continuar con el proceso de planificación de compras.</p>

        <p>Gracias por utilizar nuestro sistema de Planificación de Compras.</p>
        <p>Atentamente,<br>
            <strong>Sistema de Planificación de Compras | Ilustre Municipalidad de Arica</strong><br>
        </p>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no responda a este mensaje.</p>
        <p>&copy; {{ date('Y') }} Municipalidad de Arica. Todos los derechos reservados.</p>
    </div>
</body>
</html> 