<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EvaluadorProduccion\EvaluadorProduccionController;



Route::group([
    'middleware' =>['api', 'auth:api', 'role:Evaluador Produccion'],
    'prefix' => 'evaluadorProduccion'
], function () {
    // producciones académicas
    Route::get('obtener-producciones', [EvaluadorProduccionController::class, 'obtenerProducciones']);
    Route::get('ver-producciones-por-usuario/{user_id}', [EvaluadorProduccionController::class, 'verProduccionesPorUsuario']);
    Route::put('actualizar-produccion/{documento_id}', [EvaluadorProduccionController::class, 'actualizarEstadoDocumento']);


});