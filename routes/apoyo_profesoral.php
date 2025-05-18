<?php

// Importa la clase Route desde el espacio de nombres Illuminate\Support\Facades

use App\Http\Controllers\ApoyoProfesoral\VerificacionDocumentosController;
use Illuminate\Support\Facades\Route;

// Define un grupo de rutas con configuraciones específicas
Route::group([
    // Aplica los middlewares 'api', 'auth:api' y 'role:Apoyo Profesoral' para proteger las rutas
    'middleware' => 'api', 'auth:api', 'role:Apoyo Profesoral',
    // Establece un prefijo 'aspirante' para las rutas dentro de este grupo
    'prefix' => 'apoyoProfesoral',
], function () {

    Route::get('obtener-documentos/{tipo}', [VerificacionDocumentosController::class, 'obtenerDocumentosPorTipoYEstado']);
    Route::put('actualizar-documentos/{documento_id}', [VerificacionDocumentosController::class, 'actualizarEstadoDocumento']);

    
});
