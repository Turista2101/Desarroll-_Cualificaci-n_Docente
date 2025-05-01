<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TiposProduccionAcademica\MostrarTiposController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'tipos_produccion_academica'
], function () {
    // Rutas para obtener los tipos de productos académicos y ámbitos de divulgación
    Route::get('productos_academicos', [MostrarTiposController::class, 'obtenerProductosAcademicos']);
    Route::get('ambitos_divulgacion', [MostrarTiposController::class, 'obtenerAmbitoDivulgacion']);
    Route::get('ambitos_divulgacion/{id_producto_academico}', [MostrarTiposController::class, 'obtenerAmbitoDivulgacionPorProductoAcademico']);

});

