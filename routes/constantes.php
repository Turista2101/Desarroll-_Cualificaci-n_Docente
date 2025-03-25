<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Constantes\ConstantsController;


Route::group([
    'middleware' => 'api',
    'prefix' => 'constantes'
], function () {
    
    Route::get('codigos-ciiu', [ConstantsController::class, 'getCodigosCiuu']);
    Route::get('estado-civil', [ConstantsController::class, 'getEstadocivil']);
    Route::get('genero', [ConstantsController::class, 'getGenero']);
    Route::get('tipo-identificacion', [ConstantsController::class, 'getTipoIdentificacion']);
    Route::get('categoria-libreta-militar', [ConstantsController::class, 'getCategoriaLibretaMilitar']);
    Route::get('estado-afiliacion', [ConstantsController::class, 'getEstadoAfilicacion']);
    Route::get('tipo-afiliacion', [ConstantsController::class, 'getTipoAfiliacion']);
    Route::get('tipo-afiliado', [ConstantsController::class, 'getTipoAfiliado']);
    Route::get('tipo-documento',[ConstantsController::class , 'getTipoDocumento']);
   
});