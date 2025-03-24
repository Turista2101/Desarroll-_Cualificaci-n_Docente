<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ubicaciones\UbicacionController;


Route::group([
    'middleware' => 'api',
    'prefix' => 'ubicaciones'
], function () {
    Route::get('paises', [UbicacionController::class, 'obtenerPaises']);
    Route::get('departamentos', [UbicacionController::class, 'obtenerDepartamentos']);
    Route::get('municipios', [UbicacionController::class, 'obtenerMunicipios']);
    Route::get('departamentos/{pais_id}', [UbicacionController::class, 'obtenerDepartamentosPorPais']);
    Route::get('municipios/{departamento_id}', [UbicacionController::class, 'obtenerMunicipiosPorDepartamento']);
});