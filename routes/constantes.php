<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Constantes\ConstantesController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'constantes'
], function () {
    
    // Constantes Usuario
    Route::get('tipos-documento', [ConstantesController::class, 'obtenerTiposDocumento']);
    Route::get('estado-civil', [ConstantesController::class, 'obtenerEstadoCivil']);
    Route::get('genero', [ConstantesController::class, 'obtenerGenero']);

    // Constantes Rut
    Route::get('tipo-persona', [ConstantesController::class, 'obtenerTipoPersona']);
    Route::get('codigo-ciiu', [ConstantesController::class, 'obtenerCodigoCiiu']);

    // Constantes Eps
    Route::get('estado-afiliacion', [ConstantesController::class, 'obtenerEstadoAfiliacionEps']);
    Route::get('tipo-afiliacion', [ConstantesController::class, 'obtenerTipoAfiliacionEps']);
    Route::get('tipo-afiliado', [ConstantesController::class, 'obtenerTipoAfiliadoEps']);

    // Constantes Informacion Contacto
    Route::get('categoria-libreta-militar', [ConstantesController::class, 'obtenerTipoLibretaMilitar']);

    // Constantes Agregar Estudio
    Route::get('tipos-estudio', [ConstantesController::class, 'obtenerTipoEstudio']);

    // Constantes Agregar Experiencia
    Route::get('tipos-experiencia', [ConstantesController::class, 'obtenerTipoExperiencia']);

    // Constantes Agregar Idioma
    Route::get('niveles-idioma', [ConstantesController::class, 'obtenerNivelIdioma']);
    
});
