<?php

use App\Http\Controllers\TalentoHumano\ContratacionController;
use App\Http\Controllers\TalentoHumano\ConvocatoriaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TalentoHumano\PostulacionController;

Route::group([
    'middleware' =>['api', 'auth:api', 'role:Talento Humano'],
    'prefix' => 'talentoHumano'
], function () {
    // convocatorias
    Route::get('obtener-convocatorias',[ConvocatoriaController::class, 'obtenerConvocatorias']);
    Route::get('obtener-convocatoria/{id}',[ConvocatoriaController::class, 'obtenerConvocatoriaPorId']);
    Route::post('crear-convocatoria',[ConvocatoriaController::class, 'crearConvocatoria']);
    Route::put('actualizar-convocatoria/{id}',[ConvocatoriaController::class, 'actualizarConvocatoria']);
    Route::delete('eliminar-convocatoria/{id}',[ConvocatoriaController::class, 'eliminarConvocatoria']);

    //postulaciones
    Route::get('obtener-postulaciones',[PostulacionController::class, 'obtenerPostulaciones']);
    Route::get('obtener-postulaciones-convocatoria/{idConvocatoria}',[PostulacionController::class, 'obtenerPorConvocatoria']);
    Route::delete('eliminar-postulacion/{idPostulacion}',[PostulacionController::class, 'eliminarPostulacion']);
    Route::put('actualizar-postulacion/{idPostulacion}',[PostulacionController::class, 'actualizarEstadoPostulacion']);
    Route::get('hoja-de-vida-pdf/{idConvocatoria}/{idUsuario}', [PostulacionController::class, 'generarHojaDeVidaPDF']);
    
    //contrataciones
    Route::post('crear-contratacion/{user_id}',[ContratacionController::class, 'crearContratacion']);
    Route::put('actualizar-contratacion/{id_contratacion}',[ContratacionController::class, 'actualizarContratacion']);
    Route::delete('eliminar-contratacion/{id}',[ContratacionController::class, 'eliminarContratacion']);
    Route::get('obtener-contratacion/{id_contratacion}',[ContratacionController::class, 'obtenerContratacionPorId']);
    Route::get('obtener-contrataciones',[ContratacionController::class, 'obtenerTodasLasContrataciones']);
    



     



});
