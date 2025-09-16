<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denuncia - {{ $complaint->folio }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }

        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            color: #7f8c8d;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            background-color: #3498db;
            color: white;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 14px;
        }

        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 10px 5px 0;
            width: 30%;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            padding: 5px 0;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
        }

        .narrative {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin-top: 10px;
            text-align: justify;
            line-height: 1.6;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #ecf0f1;
            padding-top: 15px;
        }

        .witnesses-list {
            margin-top: 10px;
        }

        .witness-item {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 8px;
            border-left: 3px solid #e74c3c;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>DENUNCIA</h1>
        <h2>Folio: {{ $complaint->folio }}</h2>
        <p><strong>Fecha de creación:</strong> {{ $complaint->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Información del Denunciante -->
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL DENUNCIANTE</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre:</div>
                <div class="info-value">{{ $complaint->complainant->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">RUT:</div>
                <div class="info-value">{{ $complaint->complainant->rut }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $complaint->complainant->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Teléfono:</div>
                <div class="info-value">{{ $complaint->complainant->phone }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Dirección:</div>
                <div class="info-value">{{ $complaint->complainant->address }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Cargo:</div>
                <div class="info-value">{{ $complaint->complainant->charge }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Unidad:</div>
                <div class="info-value">{{ $complaint->complainant->unit }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Función:</div>
                <div class="info-value">{{ $complaint->complainant->function }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Grado:</div>
                <div class="info-value">{{ $complaint->complainant->grade }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Nacimiento:</div>
                <div class="info-value">
                    {{ $complaint->complainant->birthdate ? \Carbon\Carbon::parse($complaint->complainant->birthdate)->format('d/m/Y') : 'No especificada' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Ingreso:</div>
                <div class="info-value">
                    {{ $complaint->complainant->entry_date ? \Carbon\Carbon::parse($complaint->complainant->entry_date)->format('d/m/Y') : 'No especificada' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Estado Contractual:</div>
                <div class="info-value">{{ $complaint->complainant->contractual_status }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Es Víctima:</div>
                <div class="info-value">{{ $complaint->complainant->is_victim ? 'Sí' : 'No' }}</div>
            </div>
            @if ($complaint->complainant->typeDependency)
                <div class="info-row">
                    <div class="info-label">Tipo de Dependencia:</div>
                    <div class="info-value">{{ $complaint->complainant->typeDependency->name ?? 'No especificada' }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Información del Denunciado -->
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL DENUNCIADO</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre:</div>
                <div class="info-value">{{ $complaint->denounced->name ?? 'No especificado' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">RUT:</div>
                <div class="info-value">{{ $complaint->denounced->rut ?? 'No especificado' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $complaint->denounced->email ?? 'No especificado' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Cargo:</div>
                <div class="info-value">{{ $complaint->denounced->charge ?? 'No especificado' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Unidad:</div>
                <div class="info-value">{{ $complaint->denounced->unit ?? 'No especificada' }}</div>
            </div>
        </div>
    </div>

    <!-- Información de la Denuncia -->
    <div class="section">
        <div class="section-title">DETALLES DE LA DENUNCIA</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Tipo de Denuncia:</div>
                <div class="info-value">{{ $complaint->typeComplaint->name ?? 'No especificado' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nivel Jerárquico:</div>
                <div class="info-value">{{ $complaint->hierarchicalLevel->name ?? 'No especificado' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Relación Laboral:</div>
                <div class="info-value">{{ $complaint->workRelationship->name ?? 'No especificada' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Relación con Supervisor:</div>
                <div class="info-value">{{ $complaint->supervisorRelationship->name ?? 'No especificada' }}</div>
            </div>
        </div>
    </div>

    <!-- Narrativa de Circunstancias -->
    <div class="section">
        <div class="section-title">NARRATIVA DE CIRCUNSTANCIAS</div>
        <div class="narrative">
            {{ $complaint->circumstances_narrative }}
        </div>
    </div>

    <!-- Narrativa de Consecuencias -->
    <div class="section">
        <div class="section-title">NARRATIVA DE CONSECUENCIAS</div>
        <div class="narrative">
            {{ $complaint->consequences_narrative }}
        </div>
    </div>

    <!-- Testigos -->
    @if ($complaint->witnesses && $complaint->witnesses->count() > 0)
        <div class="section">
            <div class="section-title">TESTIGOS</div>
            <div class="witnesses-list">
                @foreach ($complaint->witnesses as $witness)
                    <div class="witness-item">
                        <strong>{{ $witness->name }}</strong><br>
                        RUT: {{ $witness->rut }}<br>
                        Email: {{ $witness->email }}<br>
                        Teléfono: {{ $witness->phone }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Token: {{ $complaint->token }}</p>
        @if(isset($downloadedBy))
        <p>Descargado por: {{ $downloadedBy }} el {{ $downloadedAt->format('d/m/Y H:i') }}</p>
        @endif
    </div>
</body>

</html>
