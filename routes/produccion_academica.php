<?php
// Importa la clase Route desde el espacio de nombres Illuminate\Support\Facades
use Illuminate\Support\Facades\Route;
// Importa el controlador MostrarTiposController para manejar las rutas relacionadas con tipos de producción académica
use App\Http\Controllers\TiposProduccionAcademica\MostrarTiposController;
// Define un grupo de rutas con configuraciones específicas para tipos de producción académica
Route::group([
    // Aplica el middleware 'api' para proteger las rutas
    'middleware' => 'api',
    // Establece un prefijo 'tipos_produccion_academica' para las rutas dentro de este grupo
    'prefix' => 'tipos_produccion_academica'
], function () {
    // Rutas para obtener los tipos de productos académicos y ámbitos de divulgación
    Route::get('productos_academicos', [MostrarTiposController::class, 'obtenerProductosAcademicos']);
    // Ruta para obtener los ámbitos de divulgación
    Route::get('ambitos_divulgacion', [MostrarTiposController::class, 'obtenerAmbitoDivulgacion']);
    // Ruta para obtener los ámbitos de divulgación asociados a un producto académico específico
    Route::get('ambitos_divulgacion/{id_producto_academico}', [MostrarTiposController::class, 'obtenerAmbitoDivulgacionPorProductoAcademico']);

});

