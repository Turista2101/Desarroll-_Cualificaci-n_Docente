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
    Route::get('listar-roles', [RoleController::class, 'listarRoles']);
    // Route::post('crear-rol', [RoleController::class, 'crearRol']);
    Route::post('asignar-rol', [RoleController::class, 'asignarRol']);
    Route::post('remover-rol/{id}', [RoleController::class, 'removerRol']);
    Route::put('actualizar-rol', [RoleController::class, 'actualizarRol']);
    // Route::delete('eliminar-rol', [RoleController::class, 'eliminarRol']);
    
    // Rutas de subir un archivo CSV para ubicaciones
    Route::post('uploadCsv', [UbicacionController::class, 'uploadCsv']);
    // Rutas de usuarios
    Route::get('listar-usuarios', [UserController::class, 'listarUsuarios']);
    Route::put('editar-usuario/{id}', [UserController::class, 'editarUsuario']);
    Route::delete('eliminar-usuario/{id}', [UserController::class, 'eliminarUsuario']);

    
    
});