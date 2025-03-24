<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('registrar', [AuthController::class, 'registrar']);
    Route::post('iniciarSesion', [AuthController::class, 'iniciarSesion']);
    Route::post('restablecerContraseña/{id}', [AuthController::class, 'restablecerContraseña']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('cerrarSesion', [AuthController::class, 'cerrarSesion']);
        Route::get('obtenerUsuarioAutenticado', [AuthController::class, 'obtenerUsuarioAutenticado']);
        Route::post('actualizarContrasena/{id}', [AuthController::class, 'actualizarContrasena']);
    });
});