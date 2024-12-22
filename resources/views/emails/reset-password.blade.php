<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Restablecer Contraseña</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }

            .container {
                width: 100%;
                max-width: 100%;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .header {
                background-color: #0043b0;
                color: white;
                padding: 10px;
                border-radius: 8px 8px 0 0;
                text-align: center;
            }

            .header img {
                max-width: 150px;
                margin-bottom: 10px;
            }

            .header h2 {
                text-align: center;
                margin-bottom: 0;
            }

            .content {
                padding: 20px;
                font-size: 16px;
                line-height: 1.6;
                color: #333;
            }

            .content p,
            .content a {
                margin-left: 20px;
            }

            .button {
                display: inline-block;
                background-color: #fff;
                color: #0043b0 !important;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                font-size: 16px;
                margin-top: 20px;
                border-color: #0043b0;
                border-style: solid;
                border-width: 1px;
            }

            .button:hover {
                background-color: #00308d;
            }

            .footer {
                text-align: center;
                font-size: 12px;
                color: #777;
                margin-top: 20px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="header">
                <img src="{{ $logoUrl }}" alt="Logo" />
                <h2>Restablecer tu contraseña</h2>
            </div>
            <div class="content">
                <p style="margin-top: 20px">Hola,</p>
                <p>
                    Hemos recibido una solicitud para restablecer tu contraseña.
                    Si no fuiste tú quien solicitó el cambio, por favor ignora
                    este mensaje.
                </p>
                <p>
                    Si deseas restablecer tu contraseña, haz clic en el
                    siguiente botón:
                </p>
                <a href="{{ $resetUrl }}" class="button"
                    >Restablecer Contraseña</a
                >
                <p>Este enlace expirará en 60 minutos.</p>
            </div>
            <div class="footer">
                <p>
                    Si no has solicitado este cambio, por favor ignora este
                    mensaje.
                </p>
                <p>
                    &copy; {{ date("Y") }} {{ config("app.name") }}. Todos los
                    derechos reservados.
                </p>
            </div>
        </div>
    </body>
</html>
