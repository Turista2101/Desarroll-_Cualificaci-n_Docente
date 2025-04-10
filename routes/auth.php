<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('registrar-usuario', [AuthController::class, 'registrar']);
    Route::post('iniciar-sesion', [AuthController::class, 'iniciarSesion']);
    Route::post('restablecer-contrasena', [AuthController::class, 'restablecerContrasena']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('cerrar-sesion', [AuthController::class, 'cerrarSesion']);
        Route::get('obtener-usuario-autenticado', [AuthController::class, 'obtenerUsuarioAutenticado']);
        Route::post('actualizar-contrasena/{id}', [AuthController::class, 'actualizarContrasena']);
        Route::post('actualizar-usuario', [AuthController::class, 'actualizarUsuario']);
    });
});