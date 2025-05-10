<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Formulario de Denuncia - {{ $complaint->folio }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 40px;
        }

        h3 {
            background-color: #004080;
            color: white;
            padding: 6px 10px;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        td,
        th {
            border: 1px solid #999;
            padding: 8px 6px;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .titulo {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }

        .subtitulo {
            text-align: center;
            font-size: 14px;
            margin-top: 2px;
        }

        .logo {
            max-height: 70px;
        }

        .firm-box {
            min-height: 60px;
            border-bottom: 1px solid #999;
            padding: 10px 0;
            display: inline-block;
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-table {
            margin-bottom: 0;
        }

        .footer-info {
            font-size: 11px;
            color: #666;
            margin-top: 60px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .footer-info table {
            width: 100%;
            border: none;
            margin-bottom: 6px;
        }

        .footer-info td {
            border: none;
            padding: 4px 2px;
        }

        .footer-info p {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
        }

        .page_break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <table class="header-table">
        <tr style="height: 100px;">
            <td style="width: 20%; text-align: center; vertical-align: middle;">
                <img src="{{ public_path('assets/img/logos/logo-azul-2.png') }}" class="logo" alt="Logo"
                    style="max-height: 90px;">
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <p class="titulo">FORMULARIO DE LA DENUNCIA</p>
                <p class="subtitulo">FOLIO: {{ $complaint->folio }}</p>
            </td>
        </tr>
    </table>

    <h3>I. IDENTIFICACIÓN DE LA DENUNCIA</h3>
    <table>
        <tr>
            <th>Fecha de creación</th>
            <td>{{ $complaint->date->format('d-m-Y') }}</td>
            <th>Hora</th>
            <td>{{ $complaint->date->format('h:m:s') }}</td>
        </tr>
        <tr>
            <th>Tipo de denuncia</th>
            <td colspan="3">{{ $complaint->typeComplaint->name }}</td>
        </tr>
        <tr>
            <th>Dependencia</th>
            <td colspan="3">{{ $complaint->complainant->dependence->name }}</td>
        </tr>
    </table>

    <h3>DATOS DEL DENUNCIANTE</h3>
    <table>
        <tr>
            <th>Nombre</th>
            <td colspan="3">{{ $complaint->complainant->name }}</td>
        </tr>
        <tr>
            <th>Domicilio</th>
            <td colspan="3">{{ $complaint->complainant->address }}</td>
        </tr>
        <tr>
            <th>C.I.</th>
            <td>{{ $complaint->complainant->rut }}</td>
            <th>Teléfono</th>
            <td>{{ $complaint->complainant->phone }}</td>
        </tr>
        <tr>
            <th>Cargo</th>
            <td>{{ $complaint->complainant->charge }}</td>
            <th>Correo</th>
            <td>{{ $complaint->complainant->email }}</td>
        </tr>
        <tr>
            <th>Unidad de desempeño</th>
            <td>{{ $complaint->complainant->unit }}</td>
            <th>Función</th>
            <td>{{ $complaint->complainant->function }}</td>
        </tr>
        <tr>
            <th>Grado</th>
            <td colspan="3">{{ $complaint->complainant->grade }}</td>
        </tr>
    </table>

    <h3>DATOS DEL DENUNCIADO</h3>
    <table>
        <tr>
            <th>Nombre</th>
            <td colspan="3">{{ $complaint->denounced->name }}</td>
        </tr>
        <tr>
            <th>Domicilio</th>
            <td colspan="3">{{ $complaint->denounced->address }}</td>
        </tr>
        <tr>
            <th>C.I.</th>
            <td>{{ $complaint->denounced->rut }}</td>
            <th>Teléfono</th>
            <td>{{ $complaint->denounced->phone }}</td>
        </tr>
        <tr>
            <th>Cargo</th>
            <td>{{ $complaint->denounced->charge }}</td>
            <th>Correo</th>
            <td>{{ $complaint->denounced->email }}</td>
        </tr>
        <tr>
            <th>Unidad de desempeño</th>
            <td>{{ $complaint->denounced->unit }}</td>
            <th>Función</th>
            <td>{{ $complaint->denounced->function }}</td>
        </tr>
        <tr>
            <th>Grado</th>
            <td colspan="3">{{ $complaint->denounced->grade }}</td>
        </tr>
    </table>

    <h3>II. INFORMACIÓN COMPLEMENTARIA</h3>
    <table>
        <tr>
            <th> Nivel jerárquico del/la denunciado/a respecto de la víctima</th>
            <td colspan="3">{{ ucwords(str_replace('_', ' ', $complaint->hierarchical_level)) }}</td>
        </tr>
        <tr>
            <th>¿El/la denunciado/a trabaja directamente con la víctima? </th>
            <td>{{ ucwords($complaint->work_directly) }}</td>
            <th>¿El/la denunciado/a es su jefatura superior inmediata?</th>
            <td>{{ ucwords($complaint->immediate_leadership) }}</td>
        </tr>
    </table>

    <div class="page_break"></div>
    <h3>III. NARRACIÓN DE LAS CIRCUNSTANCIAS DE LOS HECHOS</h3>
    <table>
        <tr>
            <td>{{ $complaint->narration_facts }}</td>
        </tr>
    </table>
    <div class="page_break"></div>
    <h3>IV. NARRACIÓN DE LAS CONSECUENCIAS EN LA VÍCTIMA</h3>
    <table>
        <tr>
            <td>{{ $complaint->narration_consequences }}</td>
        </tr>
    </table>

    <h3>DATOS DE TESTIGOS</h3>
    @forelse($complaint->witnesses as $witness)
        <table style="margin-bottom: 5px;">
            <tr>
                <th colspan="2" style="background-color: #f0f0f0; text-align: left; padding: 5px 10px;">
                    Testigo N° {{ $loop->iteration }}
                </th>
            </tr>
            <tr>
                <th style="width: 25%;">Nombre</th>
                <td>{{ $witness->name }}</td>
            </tr>
            <tr>
                <th>Teléfono</th>
                <td>{{ $witness->phone ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <th>Correo</th>
                <td>{{ $witness->email ?? 'No especificado' }}</td>
            </tr>
        </table>
        @if (!$loop->last)
            <div style="margin: 5px 0;"></div>
        @endif
    @empty
        <table>
            <tr>
                <td colspan="2" style="text-align: center; padding: 15px;">No hay testigos registrados</td>
            </tr>
        </table>
    @endforelse

    <div class="page_break"></div>
    <h3>FIRMA</h3>
    <div>
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center; border: none;">
                    <div class="firm-box" style="margin: 0 auto; border-bottom: 1px solid #999; padding: 10px 0;">
                        @if ($complaint->signature)
                            <img src="{{ public_path('storage/signatures/' . basename($complaint->signature)) }}"
                                alt="Firma del Denunciante"
                                style="max-width: 100%; height: auto; filter: grayscale(100%) brightness(0.8);">
                        @endif
                    </div>
                    <p
                        style="margin-top: 5px; font-size: 12px; font-family: 'Brush Script MT', cursive, sans-serif; color: #333;">
                        Firma del Denunciante
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Sección discreta de emisión -->
    <div class="footer-info">
        <p>
            Documento generado automáticamente por el sistema el {{ now()->format('d/m/Y H:i:s') }}.
        </p>
    </div>

</body>

</html>
