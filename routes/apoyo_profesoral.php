<?php

// Importa la clase Route desde el espacio de nombres Illuminate\Support\Facades
use Illuminate\Support\Facades\Route;
// Define un grupo de rutas con configuraciones específicas
Route::group([
    // Aplica los middlewares 'api', 'auth:api' y 'role:Apoyo Profesoral' para proteger las rutas
    'middleware' => ['api', 'auth:api', 'role:Apoyo Profesoral'],
    // Establece un prefijo 'aspirante' para las rutas dentro de este grupo
    'prefix' => 'aspirante'
], function () {
// Aquí se definirán las rutas específicas para este grupo
    
});
