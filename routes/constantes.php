<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Constantes\ConstantsController;


Route::group([
    'middleware' => 'api',
    'prefix' => 'constantes'
], function () {
    
    // Route::get('enviar', [ConstantsController::class, 'getConstants']);
   
});