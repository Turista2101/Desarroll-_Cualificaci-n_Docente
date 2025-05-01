<?php

namespace App\Http\Controllers\Docente;

use App\Services\CalculoPuntajeDocenteService;
use App\Models\Usuario\User;

class PuntajeController
{
    public function evaluarYGuardarPuntaje($userId, CalculoPuntajeDocenteService $servicio)
    {
        // Cargar usuario con relaciones necesarias
        $user = User::with([
            'contratacionUsuario',
            'estudiosUsuario.documentosEstudio',
            'idiomasUsuario.documentosIdioma',
            'experienciasUsuario.documentosExperiencia',
            'produccionAcademicaUsuario.documentosProduccionAcademica',
            'evaluacionDocenteUsuario',
        ])->findOrFail($userId);

        // Evaluar categoría
        $resultado = $servicio->evaluar($user);

        // Guardar o actualizar puntaje en tabla puntajes
        $user->puntajeUsuario()->updateOrCreate(
            ['user_id' => $user->id],
            ['puntaje_total' => $resultado['puntaje_total']]
        );

        // Devolver resultado con detalle
        return response()->json([
            'mensaje' => 'Evaluación completada.',
            'resultado' => $resultado
        ], 200);
    }
}
