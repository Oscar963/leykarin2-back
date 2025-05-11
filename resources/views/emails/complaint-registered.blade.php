<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comprobante de la Denuncia</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div
        style="width: 100%; max-width: 100%; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">

        <div
            style="background-color: #0043b0; color: white; padding: 10px; border-radius: 8px 8px 0 0; text-align: center;">
            <img src="{{ $logoUrl }}" alt="Logo de {{ config('app.name') }}"
                style="max-width: 150px; margin-bottom: 10px;" />
            <h2 style="margin: 0;">Confirmación de Denuncia</h2>
        </div>

        <div style="padding: 20px; font-size: 16px; line-height: 1.6; color: #333;">
            <p style="margin-top: 20px; margin-left: 20px;">Estimado/a {{ $complaint->complainant->name }},</p>
            <p style="margin-left: 20px;">
                Hemos recibido su denuncia y queremos confirmar que ha sido registrada exitosamente en nuestro sistema.
            </p>

            <div style="background-color: #f8f9fa; border-radius: 5px; padding: 15px; margin: 20px;">
                <h3 style="color: #0043b0; margin-top: 0;">Detalles de la Denuncia</h3>
                <ul style="list-style: none; padding-left: 0;">
                    <li style="margin-bottom: 10px;"><strong style="color: #0043b0;">Folio:</strong>
                        {{ $complaint->folio }}</li>
                    <li style="margin-bottom: 10px;"><strong style="color: #0043b0;">Fecha:</strong>
                        {{ $complaint->date->format('d/m/Y') }}</li>
                    <li style="margin-bottom: 10px;"><strong style="color: #0043b0;">Hora:</strong>
                        {{ $complaint->date->format('H:i:s') }}</li>
                    <li style="margin-bottom: 10px;"><strong style="color: #0043b0;">Tipo de Denuncia:</strong>
                        {{ $complaint->typeComplaint->name }}</li>
                    <li style="margin-bottom: 10px;"><strong style="color: #0043b0;">Dependencia:</strong>
                        {{ $complaint->complainant->dependence->name }}</li>
                </ul>
            </div>

            <div style="background-color: #f8f9fa; border-radius: 5px; padding: 15px; margin: 20px;">
                <h3 style="color: #0043b0; margin-top: 0;">Información del Denunciado</h3>
                <ul style="list-style: none; padding-left: 0;">
                    <li style="margin-bottom: 10px;"><strong style="color: #0043b0;">Nombre:</strong>
                        {{ $complaint->denounced->name }}</li>
                    <li style="margin-bottom: 10px;"><strong style="color: #0043b0;">Cargo:</strong>
                        {{ $complaint->denounced->charge }}</li>
                    <li style="margin-bottom: 10px;"><strong style="color: #0043b0;">Unidad:</strong>
                        {{ $complaint->denounced->unit }}</li>
                </ul>
            </div>

            <p style="margin-left: 20px;">
                Su denuncia será procesada según los procedimientos establecidos. Para cualquier consulta, puede
                contactarnos respondiendo a este correo.
            </p>
        </div>

        <div style="text-align: center; font-size: 12px; color: #777; margin-top: 20px;">
            <p>Este es un correo automático, por favor no responda a este mensaje.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>

    </div>
</body>

</html>
