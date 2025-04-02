<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Aspirante\InformacionContactoController;
use App\Http\Controllers\Aspirante\EpsController;
use App\Http\Controllers\Aspirante\IdiomaController;
use App\Http\Controllers\Aspirante\ExperienciaController;
use App\Http\Controllers\Aspirante\ProduccionAcademicaController;
use App\Http\Controllers\Aspirante\EstudioController;

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

    // idioma
    Route::post('crear-idioma', [IdiomaController::class, 'crearIdioma']);

    //Experiencia
    Route::post('crear-experiencia', [ExperienciaController::class, 'crearExperiencia']);

    //Produccion Academica
    Route::post('crear-produccion', [ProduccionAcademicaController::class, 'crearProduccion']);

    //Estudios
    Route::post('crear-estudio', [EstudioController::class, 'crearEstudio']);
    

    


});