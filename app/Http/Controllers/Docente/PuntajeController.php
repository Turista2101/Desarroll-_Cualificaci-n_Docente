<?php

namespace App\Http\Controllers\Docente;

use App\Services\CalculoPuntajeDocenteService;
use Illuminate\Http\Request;

class PuntajeController
{
    public function evaluarYGuardarPuntaje(Request $request, CalculoPuntajeDocenteService $servicio)
    {
        // Obtener el usuario autenticado
        $user = $request->user();

        if (!$user) {
            return response()->json(['mensaje' => 'Usuario no autenticado.'], 401);
        }

        // Cargar el usuario con las relaciones necesarias
        $user->load([
            'contratacionUsuario',
            'estudiosUsuario.documentosEstudio',
            'idiomasUsuario.documentosIdioma',
            'experienciasUsuario.documentosExperiencia',
            'produccionAcademicaUsuario.documentosProduccionAcademica',
            'evaluacionDocenteUsuario',
        ]);

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
