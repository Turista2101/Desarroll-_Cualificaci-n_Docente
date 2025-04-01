<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('registrar-usuario', [AuthController::class, 'registrar']);
    Route::post('iniciar-sesion', [AuthController::class, 'iniciarSesion']);
    Route::post('restablecer-contraseña/{id}', [AuthController::class, 'restablecerContraseña']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('cerrar-sesion', [AuthController::class, 'cerrarSesion']);
        Route::get('obtener-usuario-utenticado', [AuthController::class, 'obtenerUsuarioAutenticado']);
        Route::post('actualizar-contrasena/{id}', [AuthController::class, 'actualizarContrasena']);
    });
});