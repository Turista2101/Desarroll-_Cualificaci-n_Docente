<?php


use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api', 'auth:api', 'role:Talento Humano',
    'prefix' => 'aspirante'
], function () {


});
