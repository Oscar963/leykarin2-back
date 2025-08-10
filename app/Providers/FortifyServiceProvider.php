<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureFortify();
        $this->configureRateLimiting();
        $this->registerAuthRoutes();
    }

    /**
     * Configura las acciones y características principales de Fortify.
     */
    protected function configureFortify(): void
    {
        // Vincula las acciones personalizadas para la lógica de negocio.
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Define 'rut' como el campo de autenticación principal en toda la aplicación.
        Fortify::username('rut');

        // Para una API sin vistas, en lugar de renderizar una vista para el desafío 2FA,
        // lanzamos un error 423 (Locked). El frontend interpretará este código
        // para mostrar la pantalla de ingreso del código 2FA.
        Fortify::twoFactorChallengeView(function () {
            abort(423, 'Two-Factor authentication required.');
        });
    }

    /**
     * Define los limitadores de peticiones para la autenticación.
     */
    protected function configureRateLimiting(): void
    {
        // El Rate Limiter para el login se gestiona en config/fortify.php y AuthController.
        RateLimiter::for('two-factor', function (Request $request) {
            $key = $request->session()->get('login.id') ?: $request->ip();
            return Limit::perMinute(5)->by($key);
        });
    }

    /**
     * Registra las rutas personalizadas para la autenticación, sobrescribiendo
     * el comportamiento por defecto de Fortify para que apunten a nuestro controlador de API.
     */
    protected function registerAuthRoutes(): void
    {
        Route::group([
            'prefix' => 'api/v1/auth',
            'middleware' => ['web'], // Las rutas de autenticación de Fortify requieren el middleware 'web' para las sesiones.
        ], function () {
            Route::post('/login', [AuthController::class, 'login'])
                ->name('login');

            Route::post('/logout', [AuthController::class, 'logout'])
                ->middleware('auth:sanctum') // Proteger la ruta de logout.
                ->name('logout');

            Route::post('/two-factor-challenge', [AuthController::class, 'twoFactorChallenge'])
                ->name('two-factor.login');
        });
    }
}
