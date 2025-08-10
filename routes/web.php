<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rutas de Clave Única (Socialite) deben usar middleware 'web' para manejar la sesión y el estado de OAuth
Route::prefix('auth')->group(function () {
    Route::get('/claveunica/redirect', [AuthController::class, 'redirectToClaveUnica'])->name('auth.claveunica.redirect');
    Route::get('/claveunica/callback', [AuthController::class, 'handleClaveUnicaCallback'])->name('auth.claveunica.callback');
});