<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Ubicaciones\UbicacionController;
use App\Http\Controllers\Admin\UserController;

Route::group([
    'middleware' => 'api', 'auth:api', 'role:Administrador',
    'prefix' => 'admin'
], function () {
    // Rutas de roles
    Route::get('listarRoles', [RoleController::class, 'listarRoles']);
    Route::post('crearRol', [RoleController::class, 'crearRol']);
    Route::post('asignarRol', [RoleController::class, 'asignarRol']);
    Route::post('removerRol/{id}', [RoleController::class, 'removerRol']);
    Route::put('actualizarRol', [RoleController::class, 'actualizarRol']);
    Route::delete('eliminarRol', [RoleController::class, 'eliminarRol']);
    // Rutas de subir un archivo CSV para ubicaciones
    Route::post('uploadCsv', [UbicacionController::class, 'uploadCsv']);
    // Rutas de usuarios
    Route::get('listarUsuarios', [UserController::class, 'listarUsuarios']);
    Route::put('editarUsuario/{id}', [UserController::class, 'editarUsuario']);
    Route::delete('eliminarUsuario/{id}', [UserController::class, 'eliminarUsuario']);
    
});