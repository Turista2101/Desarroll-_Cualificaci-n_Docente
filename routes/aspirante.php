<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Aspirante\InformacionContactoController;
use App\Http\Controllers\Aspirante\EpsController;

Route::group([
    'middleware' => 'api', 'auth:api', 'role:Aspirante',
    'prefix' => 'aspirante'
], function () {
    // Informacion de contacto
    Route::get('obtener-informacion-contacto', [InformacionContactoController::class, 'obtenerInformacionContacto']);
    Route::post('crear-informacion-contacto', [InformacionContactoController::class, 'crearInformacionContacto']);
    Route::put('actualizar-informacion-Contacto', [InformacionContactoController::class, 'actualizarInformacionContacto']);

    // eps
    Route::get('obtener-eps', [EpsController::class, 'obtenerEps']);
    Route::post('crear-eps', [EpsController::class, 'crearEps']);
    Route::put('actualizar-eps', [EpsController::class, 'actualizarEps']);

    // rut
    


});