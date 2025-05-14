<?php
// Importa la clase Route desde el espacio de nombres Illuminate\Support\Facades
use Illuminate\Support\Facades\Route;
// Importa el controlador de autenticación
use App\Http\Controllers\Auth\AuthController;
// Define un grupo de rutas con configuraciones específicas para autenticación
Route::group([
    // Aplica el middleware 'api' para proteger las rutas
    'middleware' => 'api',
    // Establece un prefijo 'auth' para las rutas dentro de este grupo
    'prefix' => 'auth'
], function () {
    // Ruta para registrar un nuevo usuario
    Route::post('registrar-usuario', [AuthController::class, 'registrar']);
    // Ruta para iniciar sesión
    Route::post('iniciar-sesion', [AuthController::class, 'iniciarSesion']);
    // Ruta para restablecer la contraseña
    Route::post('restablecer-contrasena', [AuthController::class, 'restablecerContrasena']);
    // ruta para restablecer la contraseña cuando se te olvido
    Route::post('restablecer-contraseña-token', [AuthController::class, 'actualizarContrasenaConToken']);
    
    // Define un subgrupo de rutas protegidas por el middleware 'auth:api'
    Route::group(['middleware' => 'auth:api'], function () {
        
        // Ruta para cerrar sesión
        Route::post('cerrar-sesion', [AuthController::class, 'cerrarSesion']);
        // Ruta para obtener los datos del usuario autenticado
        Route::get('obtener-usuario-autenticado', [AuthController::class, 'obtenerUsuarioAutenticado']);
        // Ruta para actualizar la contraseña de un usuario específico
        Route::post('actualizar-contrasena/{id}', [AuthController::class, 'actualizarContrasena']);
        // Ruta para actualizar los datos del usuario autenticado
        Route::post('actualizar-usuario', [AuthController::class, 'actualizarUsuario']);
        
    });
});