<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Aspirante\InformacionContactoController;
use App\Http\Controllers\Aspirante\EpsController;
use App\Http\Controllers\Aspirante\IdiomaController;
use App\Http\Controllers\Aspirante\ExperienciaController;
use App\Http\Controllers\Aspirante\ProduccionAcademicaController;
use App\Http\Controllers\Aspirante\EstudioController;
use App\Http\Controllers\Aspirante\RutController;
use App\Models\Aspirante\Rut;

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
    Route::get('obtener-idiomas', [IdiomaController::class, 'obtenerIdiomas']);
    Route::put('actualizar-idioma/{id}', [IdiomaController::class, 'actualizarIdioma']);
    Route::delete('eliminar-idioma/{id}', [IdiomaController::class, 'eliminarIdioma']);

    //Experiencia
    Route::post('crear-experiencia', [ExperienciaController::class, 'crearExperiencia']);
    Route::get('obtener-experiencias', [ExperienciaController::class, 'obtenerExperiencias']);
    Route::put('actualizar-experiencia/{id}', [ExperienciaController::class, 'actualizarExperiencia']);
    Route::delete('eliminar-experiencia/{id}', [ExperienciaController::class, 'eliminarExperiencia']);

    //Produccion Academica
    Route::post('crear-produccion', [ProduccionAcademicaController::class, 'crearProduccion']);
    Route::get('obtener-producciones', [ProduccionAcademicaController::class, 'obtenerProducciones']);
    Route::put('actualizar-produccion/{id}', [ProduccionAcademicaController::class, 'actualizarProduccion']);
    Route::delete('eliminar-produccion/{id}', [ProduccionAcademicaController::class, 'eliminarProduccion']);

    //Estudios
    Route::post('crear-estudio', [EstudioController::class, 'crearEstudio']);
    Route::get('obtener-estudios', [EstudioController::class, 'obtenerEstudios']);
    Route::put('actualizar-estudio/{id}', [EstudioController::class, 'actualizarEstudio']);
    Route::delete('eliminar-estudio/{id}', [EstudioController::class, 'eliminarEstudio']);

    // Rut
    //actualizar rutas
    Route::get('obtener-rut', [RutController::class, 'obtenerRut']);
    Route::post('crear-rut', [RutController::class, 'crearRut']);
    Route::put('actualizar-rut', [RutController::class, 'actualizarRut']);
    

    


});