<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <title>Denuncia {{ $complaint->folio }}</title>

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
            margin-top: 2rem
        }

        .page_break {
            page-break-before: always;
        }

        .logo_municipalidad {
            width: 170px;
        }
    </style>
</head>

<body>
    <div class="caja_grande">
        <!--Poner el/la en denunciado y denunciante, víctima no-->
        <div>
            <img class="logo_municipalidad" src="{{ public_path('assets/img/logos/logo-azul.png') }}" alt="">
            <h1 class="titulo">FORMULARIO DE LA DENUNCIA</h1>
            <h3 class="titulo">FOLIO: {{ $complaint->folio }}</h3>
            <h3 class="">I. IDENTIFICACIÓN DE LA DENUNCIA</h3>
        </div>
        <div>
            <!--Fecha y hora-->
            <div class="caja_uno">
                <!--Hacer explode a fecha y ordenarla día - mes - año-->
                @php
                    $fecha_creacion = $complaint->created_at;
                    $fecha = $fecha_creacion->format('d-m-Y');
                    $hora = $fecha_creacion->format('H:i');
                @endphp
                <p class="fecha_hora nombre">Fecha de creación</p><!--
                    -->
                <p class="fecha_hora valor quitar_borde">{{ $fecha }}</p><!--
                    -->
                <p class="fecha_hora nombre">Hora de creación</p><!--
                    -->
                <p class="fecha_hora valor">{{ $hora }}</p>
            </div>
            <!--Tipo denuncia-->
            <div>
                <p class="atributo nombre">Tipo de denuncia</p><!--
                   -->
                <p class="atributo valor_doble">{{ $complaint->typeComplaint->name ?? 'No especificado' }}</p>
            </div>

            <!--Dependencia-->
            <div>
                <p class="atributo nombre">Dependencia</p><!--
                    -->
                <p class="atributo valor_doble">{{ $complaint->complainant->typeDependency->name ?? 'No especificada' }}
                </p>
            </div>

            <!--Datos denunciante-->
            <div>
                <h3 class="titulo">DATOS DEL DENUNCIANTE</h3>
                <p class="atributo nombre primer_atributo">Nombre</p><!--
                    -->
                <p class="atributo valor_doble primer_atributo">{{ $complaint->complainant->name }}</p>

                <p class="atributo nombre">Domicilio</p><!--
                    -->
                <p class="atributo valor_doble">{{ $complaint->complainant->address ?? 'Sin información' }}</p>

                <p class="atributo nombre quitar_borde">RUT</p><!--
                    -->
                <p class="atributo valor_cuadruple quitar_borde">{{ $complaint->complainant->rut }}</p><!--
                    -->
                <p class="atributo nombre quitar_borde">Teléfono</p><!--
                    -->
                <p class="atributo valor_cuadruple">{{ $complaint->complainant->phone ?? 'Sin información' }}</p>

                <p class="atributo nombre">Cargo</p><!--
                    -->
                <p class="atributo valor_doble">{{ $complaint->complainant->charge ?? 'Sin información' }}</p>

                <p class="atributo nombre">Correo</p><!--
                    -->
                <p class="atributo valor_doble">{{ $complaint->complainant->email ?? 'Sin información' }}</p>

                <p class="atributo nombre">Unidad de desempeño</p><!--
                    -->
                <p class="atributo valor_doble">{{ $complaint->complainant->unit ?? 'Sin información' }}</p>

                <p class="atributo nombre">Función que realiza</p><!--
                    -->
                <p class="atributo valor_doble">{{ $complaint->complainant->function ?? 'Sin información' }}</p>

                <p class="atributo nombre">Grado EUR</p><!--
                    -->
                <p class="atributo valor_doble">
                    @if ($complaint->complainant->grade == null)
                        Sin información
                    @else
                        {{ $complaint->complainant->grade }}
                    @endif
                </p>
            </div>

            <!--Datos víctima-->
            @if (!$complaint->complainant->is_victim)
                <div>
                    <h3 class="titulo">DATOS DE LA VÍCTIMA</h3>
                    <p class="atributo nombre primer_atributo">Información de víctima</p><!--
                    -->
                    <p class="atributo valor_doble primer_atributo">El denunciante actúa en representación de terceros
                    </p>
                </div>
            @endif

            <!--Datos denunciado-->
            <div>
                <h3 class="titulo">DATOS DEL DENUNCIADO</h3>
                <p class="atributo nombre primer_atributo">Nombre</p><!--
                    -->
                <p class="atributo valor_doble primer_atributo">{{ $complaint->denounced->name ?? 'Sin información' }}
                </p>

                <p class="atributo nombre">Domicilio</p><!--
                    -->
                <p class="atributo valor_doble">
                    @if ($complaint->denounced->address ?? null == null)
                        Sin información
                    @else
                        {{ $complaint->denounced->address }}
                    @endif
                </p>

                <p class="atributo nombre quitar_borde">RUT</p><!--
                    -->
                <p class="atributo valor_cuadruple quitar_borde">
                    @if ($complaint->denounced->rut ?? null == null)
                        Sin información
                    @else
                        {{ $complaint->denounced->rut }}
                    @endif
                </p><!--
                    -->
                <p class="atributo nombre quitar_borde">Teléfono</p><!--
                    -->
                <p class="atributo valor_cuadruple">
                    @if ($complaint->denounced->phone ?? null == null)
                        Sin información
                    @else
                        {{ $complaint->denounced->phone }}
                    @endif
                </p>

                <p class="atributo nombre ">Cargo</p><!--
                    -->
                <p class="atributo valor_doble">{{ $complaint->denounced->charge ?? 'Sin información' }}</p>

                <p class="atributo nombre">Correo</p><!--
                    -->
                <p class="atributo valor_doble">
                    @if ($complaint->denounced->email ?? null == null)
                        Sin información
                    @else
                        {{ $complaint->denounced->email }}
                    @endif
                </p>

                <p class="atributo nombre">Unidad de desempeño</p><!--
                    -->
                <p class="atributo valor_doble">{{ $complaint->denounced->unit ?? 'Sin información' }}</p>

                <p class="atributo nombre">Función que realiza</p><!--
                    -->
                <p class="atributo valor_doble">{{ $complaint->denounced->function ?? 'Sin información' }}</p>

                <p class="atributo nombre ">Grado EUR</p><!--
                    -->
                <p class="atributo valor_doble">
                    @if ($complaint->denounced->grade ?? null == null)
                        Sin información
                    @else
                        {{ $complaint->denounced->grade }}
                    @endif
                </p>
            </div>

            <!--Nivel jerárquico, trabajo directo, jefe directo-->
            @if (!$complaint->complainant->is_victim)
                <div class="page_break">
                @else
                    <div>
            @endif

            <h3>II. INFORMACIÓN COMPLEMENTARIA</h3>
            <p class="atributo nombre_largo primer_atributo">Nivel jerárquico del denunciado con respecto a la víctima
            </p><!--
                    -->
            <p class="atributo valor_corto primer_atributo">
                {{ $complaint->hierarchicalLevel->name ?? 'No especificado' }}</p>

            <p class="atributo nombre_largo">Relación laboral con la víctima</p><!--
                    -->
            <p class="atributo valor_corto">{{ $complaint->workRelationship->name ?? 'No especificada' }}</p>

            <p class="atributo nombre_largo">Relación con supervisor</p><!--
                    -->
            <p class="atributo valor_corto">{{ $complaint->supervisorRelationship->name ?? 'No especificada' }}</p>
        </div>

        <!--Hechos y consecuencias-->
        @if (!$complaint->complainant->is_victim)
            <div>
            @else
                <div class="page_break">
        @endif
        <h3>III. NARRACIÓN DE LOS HECHOS Y CONSECUENCIAS</h3>
        <p class="textarea"><strong>Hechos:</strong> {{ $complaint->circumstances_narrative }}</p>
        <p class="textarea"><strong>Consecuencias:</strong> {{ $complaint->consequences_narrative }}</p>
    </div>

    <!--Cargar testigos-->
    @if (!$complaint->complainant->is_victim)
        <div class="page_break">
        @else
            <div>
    @endif
    @if ($complaint->witnesses && $complaint->witnesses->count() > 0)
        <h3 class="titulo">DATOS DE TESTIGOS</h3>
        @foreach ($complaint->witnesses as $witness)
            <br>
            <p class="atributo nombre primer_atributo">Nombre</p><!--
                            -->
            <p class="atributo valor_doble primer_atributo">{{ $witness->name }}</p>

            @if ($witness->phone != null)
                <p class="atributo nombre">Teléfono</p><!--
                                -->
                <p class="atributo valor_doble">{{ $witness->phone }}</p>
            @endif

            <p class="atributo nombre">Correo</p><!--
                            -->
            <p class="atributo valor_doble">{{ $witness->email }}</p> <br>
        @endforeach
    @endif
    </div>

    <!--Cargar firma-->
    @php
        $signatureFile = $complaint->files()->where('file_type', 'signature')->first();
    @endphp
    @if ($signatureFile)
        <div>
            <strong>
                <p>Firma:</p>
            </strong>
            <div class="div_imagen">
                @php
                    // Para DomPDF necesitamos la ruta absoluta del archivo
                    $signaturePath = storage_path('app/public/' . $signatureFile->path);
                @endphp
                @if (file_exists($signaturePath))
                    <img class="imagen" src="{{ $signaturePath }}" alt="" width="80%">
                @else
                    <p>Firma no disponible</p>
                @endif
            </div>
        </div>
    @endif

    </div>
    </div>
</body>

</html>
