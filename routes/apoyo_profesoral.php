<?php


use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api', 'auth:api', 'role:Apoyo Profesoral',
    'prefix' => 'aspirante'
], function () {

    
});
