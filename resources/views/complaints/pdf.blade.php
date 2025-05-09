<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <title>Comprobante de Queja</title>

    <style>
        .caja_grande {
            font-family: Helvetica;
        }

        p {
            font-size: 0.9rem;
        }

        .titulo {
            text-align: center;
            margin: 0.5rem 1rem;
        }

        .fecha_hora {
            border: 1px solid black;
            padding: 3px;
            margin: 0;
            width: 23.9%;
            display: inline-block;
        }

        .nombre {
            font-weight: bold;
            border-right: none;
            width: 23.9%;
        }

        .atributo {
            border: 1px solid black;
            padding: 3px;
            margin: 0;
            display: inline-block;
            border-top: none;
        }

        .primer_atributo {
            border-top: 1px solid black;
        }

        .valor_doble {
            border-left: none;
            width: 73.7%;
        }

        .valor_cuadruple {
            width: 23.9%;
        }

        .nombre_largo {
            width: 73.7%;
            border-right: none;
        }

        .valor_corto {
            width: 23.9%;
        }

        .quitar_borde {
            border-right: none;
        }

        .textarea {
            word-wrap: break-word;
            border: 1px solid black;
            padding: 3px;
        }

        .div_imagen {
            text-align: center;
        }

        .imagen {
            margin-top: 1rem
        }

        .page_break {
            page-break-before: always;
        }

        .logo_municipalidad {

            width: 8rem;
            height: 5rem;
        }
    </style>
</head>

<body>











    
    <div class="header">
        <h1>Comprobante de Queja</h1>
        <p>Número de Queja: {{ $complaint->id }}</p>
        <p>Fecha: {{ $complaint->created_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Información del Denunciante</div>
        <div class="info-row">
            <span class="label">Nombre:</span>
            <span>{{ $complaint->complainant->name ?? 'No especificado' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Documento:</span>
            <span>{{ $complaint->complainant->document ?? 'No especificado' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Contacto:</span>
            <span>{{ $complaint->complainant->contact ?? 'No especificado' }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Información del Denunciado</div>
        <div class="info-row">
            <span class="label">Nombre:</span>
            <span>{{ $complaint->denounced->name ?? 'No especificado' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Documento:</span>
            <span>{{ $complaint->denounced->document ?? 'No especificado' }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Detalles de la Queja</div>
        <div class="info-row">
            <span class="label">Tipo:</span>
            <span>{{ $complaint->type ?? 'No especificado' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Descripción:</span>
            <span>{{ $complaint->description ?? 'No especificada' }}</span>
        </div>
    </div>

    @if ($complaint->attachments && count($complaint->attachments) > 0)
        <div class="section">
            <div class="section-title">Archivos Adjuntos</div>
            <ul>
                @foreach ($complaint->attachments as $attachment)
                    <li>{{ $attachment->filename }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="footer">
        <p>Este documento es un comprobante oficial de la queja registrada en el sistema.</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>

</html>
