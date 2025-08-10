<?php

use App\Providers\RouteServiceProvider;
use Laravel\Fortify\Features;

return [

    /*
    |--------------------------------------------------------------------------
    | Fortify Guard
    |--------------------------------------------------------------------------
    |
    | Fortify utilizará el guard 'web' para la autenticación basada en sesiones (cookies),
    | que es el método recomendado al usar Laravel Sanctum con una SPA en el mismo dominio.
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Fortify Password Broker
    |--------------------------------------------------------------------------
    |
    | Se especifica el broker de contraseñas a utilizar para la funcionalidad
    | de reseteo de contraseñas. 'users' es el valor por defecto y el correcto
    | para la mayoría de las aplicaciones.
    |
    */

    'passwords' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    |
    | Se define 'rut' como el atributo principal para la autenticación, en lugar
    | del 'email' por defecto. Esto se alinea con la lógica de negocio de la aplicación.
    |
    */

    'username' => 'rut',

    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    |
    | Aunque el login es con 'rut', el campo 'email' sigue siendo necesario para
    | funcionalidades como la verificación de correo y el reseteo de contraseñas.
    |
    */

    'email' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Lowercase Usernames
    |--------------------------------------------------------------------------
    |
    | Se deshabilita la conversión a minúsculas para el 'username' (rut),
    | ya que el RUT puede contener una 'K' mayúscula que es sensible.
    |
    */

    'lowercase_usernames' => false,

    /*
    |--------------------------------------------------------------------------
    | Home Path
    |--------------------------------------------------------------------------
    |
    | Aunque en una API las redirecciones se manejan en el frontend, esta ruta
    | sirve como un valor por defecto para Fortify después de una operación exitosa.
    |
    */

    'home' => RouteServiceProvider::HOME,

    /*
    |--------------------------------------------------------------------------
    | Fortify Routes Prefix
    |--------------------------------------------------------------------------
    |
    | Define el prefijo para las rutas que Fortify registra automáticamente (ej. password reset).
    | Las rutas principales (login, logout) se han sobrescrito manualmente en FortifyServiceProvider.
    |
    */

    'prefix' => 'api/v1/auth',

    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Fortify Routes Middleware
    |--------------------------------------------------------------------------
    |
    | El middleware 'web' es esencial para que Fortify pueda manejar el estado
    | de la sesión (cookies, CSRF) correctamente.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Se especifican los nombres de los Rate Limiters a usar. Estos limitadores
    | se definen en FortifyServiceProvider o en RouteServiceProvider.
    |
    */

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Register View Routes
    |--------------------------------------------------------------------------
    |
    | CRÍTICO para una API: Se deshabilita el registro de rutas que devuelven
    | vistas de Blade. El frontend (Angular) se encargará de todas las vistas.
    |
    */

    'views' => false,

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Aquí se activan o desactivan las características de Fortify.
    |
    */

    'features' => [
        // El registro de usuarios se maneja por otra vía (ej. un endpoint de admin o Clave Única).
        // Features::registration(),

        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication([
            // Requiere que el usuario confirme su contraseña actual antes de
            // habilitar, deshabilitar o regenerar los códigos 2FA, como medida de seguridad.
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],

];
