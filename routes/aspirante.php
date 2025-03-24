<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Aspirante\InformacionContactoController;
use App\Http\Controllers\Aspirante\EpsController;

Route::group([
    'middleware' => 'api', 'auth:api', 'role:Aspirante',
    'prefix' => 'aspirante'
], function () {
    // Informacion de contacto
    Route::get('ObtenerInformacionContacto', [InformacionContactoController::class, 'obtenerInformacionContacto']);
    Route::post('CrearInformacionContacto', [InformacionContactoController::class, 'crearInformacionContacto']);
    Route::put('ActualizarInformacionContacto', [InformacionContactoController::class, 'actualizarInformacionContacto']);

    // eps
    Route::get('ObtenerEps', [EpsController::class, 'obtenerEps']);
    Route::post('CrearEps', [EpsController::class, 'crearEps']);
    Route::put('ActualizarEps', [EpsController::class, 'actualizarEps']);

    // rut
    


});