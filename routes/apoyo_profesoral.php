<?php

// Importa la clase Route desde el espacio de nombres Illuminate\Support\Facades

use App\Http\Controllers\ApoyoProfesoral\FiltrarDocentesController;
use App\Http\Controllers\ApoyoProfesoral\VerificacionDocumentosController;
use Illuminate\Support\Facades\Route;

// Define un grupo de rutas con configuraciones específicas
Route::group([
    'middleware' => 'api', 'auth:api', 'role:Apoyo Profesoral',
    'prefix' => 'apoyoProfesoral',
], function () {
    // Rutas para la verificación de documentos
    Route::get('obtener-documentos/{estado}', [VerificacionDocumentosController::class, 'obtenerDocumentosPorEstado']);
    Route::put('actualizar-documento/{id}', [VerificacionDocumentosController::class, 'actualizarEstadoDocumento']);
    Route::get('listar-docentes', [VerificacionDocumentosController::class, 'listarDocentes']);
    Route::get('ver-documentos-docente/{id}', [VerificacionDocumentosController::class, 'verDocumentosPorDocente']);
    // Rutas para filtrar docentes
    Route::get('filtrar-docentes-estudio/{tipo}', [FiltrarDocentesController::class, 'obtenerDocentesPorTipoEstudio']);


});
