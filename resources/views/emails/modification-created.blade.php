<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Nueva Modificación Creada</title>
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
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
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
            border-left: 4px solid #3498db;
        }

        h2 {
            color: #3498db;
            margin-top: 0;
            font-size: 24px;
            border-bottom: 2px solid #e6e9f0;
            padding-bottom: 10px;
        }

        h3 {
            color: #3498db;
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
            color: #3498db;
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
            color: #3498db;
            font-weight: bold;
        }

        .municipality-name {
            color: #2980b9;
            font-weight: bold;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
            color: white;
        }

        .status-pending {
            background-color: #f39c12;
        }

        .status-active {
            background-color: #27ae60;
        }

        .status-approved {
            background-color: #3498db;
        }

        .status-rejected {
            background-color: #e74c3c;
        }

        .status-inactive {
            background-color: #95a5a6;
        }

        .action-notice {
            background-color: #e8f4f8;
            border: 1px solid #b3d9e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #2c5f7a;
        }

        .modification-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }

        .version-highlight {
            background-color: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
        }

        .creator-info {
            background-color: #f1f8ff;
            border: 1px solid #c9e2ff;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .email-content-section {
            margin: 20px 0;
        }

        .email-content-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #f39c12;
            font-style: italic;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/img/logos/logo-blanco.png') }}" alt="Logo Municipalidad de Arica" class="logo">
        </div>

        <div class="content">
            <h2>🔄 Nueva Modificación Creada</h2>

            <p>Estimado/a Visador,</p>
            <p>Se ha creado una nueva modificación en el plan de compra <span class="highlight">{{ $modification->purchasePlan->name ?? 'No especificado' }}</span> que requiere su <strong>revisión y aprobación</strong>.</p>

            @if($modification->status === 'pending')
                <div class="status-badge status-pending">⏳ Pendiente de Revisión</div>
            @elseif($modification->status === 'active')
                <div class="status-badge status-active">✅ Activa</div>
            @elseif($modification->status === 'approved')
                <div class="status-badge status-approved">✅ Aprobada</div>
            @elseif($modification->status === 'rejected')
                <div class="status-badge status-rejected">❌ Rechazada</div>
            @else
                <div class="status-badge status-inactive">⏸️ Inactiva</div>
            @endif

            <div class="action-notice">
                <strong>⚠️ Acción Requerida:</strong> Esta modificación está pendiente de su revisión. Por favor, acceda al sistema para aprobar, rechazar o solicitar cambios adicionales.
            </div>

            <div class="details">
                <h3>📋 Detalles de la Modificación</h3>
                <p><strong>Nombre:</strong> {{ $modification->name }}</p>
                <p><strong>Descripción:</strong> {{ $modification->description }}</p>
                <p><strong>Versión:</strong> <span class="version-highlight">{{ $modification->version }}</span></p>
                <p><strong>Tipo de Modificación:</strong> {{ $modification->modificationType->name ?? 'No especificado' }}</p>
                <p><strong>Fecha de Creación:</strong> {{ \Carbon\Carbon::parse($modification->date)->format('d/m/Y') }}</p>
                <p><strong>Fecha y Hora de Registro:</strong> {{ \Carbon\Carbon::parse($modification->created_at)->format('d/m/Y H:i:s') }}</p>
            </div>

            <div class="modification-info">
                <h3>📊 Información del Plan de Compra</h3>
                <p><strong>Plan de Compra:</strong> {{ $modification->purchasePlan->name ?? 'No especificado' }}</p>
                <p><strong>Dirección:</strong> {{ $modification->purchasePlan->direction->name ?? 'No especificada' }}</p>
                <p><strong>Año:</strong> {{ $modification->purchasePlan->year ?? 'No especificado' }}</p>
                @if($modification->purchasePlan->formF1)
                <p><strong>Monto F1:</strong> ${{ number_format($modification->purchasePlan->formF1->amount ?? 0, 0, ',', '.') }}</p>
                @endif
                <p><strong>Estado del Plan:</strong> {{ $modification->purchasePlan->status ?? 'No especificado' }}</p>
            </div>

            <div class="creator-info">
                <h3>👤 Información del Solicitante</h3>
                <p><strong>Creado por:</strong> {{ $modification->createdBy->name ?? 'Usuario no especificado' }}</p>
                @if($modification->createdBy->email ?? false)
                <p><strong>Correo:</strong> {{ $modification->createdBy->email }}</p>
                @endif
                <p><strong>Fecha de Solicitud:</strong> {{ \Carbon\Carbon::parse($modification->created_at)->format('d/m/Y H:i:s') }}</p>
            </div>

            @if($emailContent)
            <div class="email-content-section">
                <h3>💬 Comentarios Adicionales</h3>
                <div class="email-content-box">
                    {!! $emailContent !!}
                </div>
            </div>
            @endif

            <p>Para revisar esta modificación, por favor ingrese al sistema de Planificación de Compras de la <span class="municipality-name">Municipalidad de Arica</span>.</p>

            <p><strong>Próximos pasos:</strong></p>
            <ul>
                <li>Revisar los detalles de la modificación</li>
                <li>Evaluar la justificación presentada</li>
                <li>Aprobar, rechazar o solicitar modificaciones</li>
                <li>Agregar comentarios si es necesario</li>
            </ul>

            <p>Gracias por su atención y prontitud en la revisión.</p>
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