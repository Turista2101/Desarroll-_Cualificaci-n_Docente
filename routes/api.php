<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InformacionContactoController; // Ensure this controller exists in the specified namespace
use App\Http\Controllers\RoleController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {

    // Rutas públicas (No requieren autenticación)
    Route::post('registrar', [AuthController::class, 'registrar']);
    Route::post('iniciarSesion', [AuthController::class, 'iniciarSesion']);
    Route::post('restablecerContraseña/{id}', [AuthController::class, 'restablecerContraseña']);

    // Rutas protegidas (Requieren autenticación con JWT)
    Route::group(['middleware' => 'auth:api'], function () {

        Route::post('cerrarSesion', [AuthController::class, 'cerrarSesion']);
        Route::get('obtenerUsuarioAutenticado', [AuthController::class, 'obtenerUsuarioAutenticado']);
        Route::post('actualizarContrasena/{id}', [AuthController::class, 'actualizarContrasena']);

    
        //Rutas abministrador
        Route::group(['middleware' => 'role:Administrador'], function () {
            // rutas de roles
            Route::get('listarRoles', [RoleController::class, 'listarRoles']);
            Route::post('crearRol', [RoleController::class, 'crearRol']);
            Route::post('asignarRol', [RoleController::class, 'asignarRol']);
            Route::post('removerRol/{id}', [RoleController::class, 'removerRol']);
            Route::put('actualizarRol', [RoleController::class, 'actualizarRol']);
            Route::delete('eliminarRol', [RoleController::class, 'eliminarRol']);
        });

    
    
    });


});