<?php

namespace App\Http\Controllers\Docente;

use App\Http\Requests\RequestDocente\RequestEvaluacionDocente\ActualizarEvaluacionDocenteRequest;
use App\Http\Requests\RequestDocente\RequestEvaluacionDocente\CrearEvaluacionDocenteRequest;
use App\Models\Docente\EvaluacionDocente;
use Illuminate\Support\Facades\DB;


class EvaluacionDocenteController
{
    // Crear una evaluación
    public function crearEvaluacionDocente(CrearEvaluacionDocenteRequest $request)
    {
        try {
            $usuarioId = $request->user()->id;

            // Verificar si ya tiene una evaluación docente
            $evaluacionExistente = EvaluacionDocente::where('user_id', $usuarioId)->first();

            if ($evaluacionExistente) {
                return response()->json([
                    'message' => 'Ya tienes una evaluación docente registrada. No puedes crear otra.',
                ], 409);
            }
            $evaluacion = DB::transaction(function () use ($request) {
               
                $datosEvaluacion = $request->validated();
                $datosEvaluacion['user_id'] = $request->user()->id;
    
                return EvaluacionDocente::create($datosEvaluacion);
            });

            return response()->json([
                'message' => 'Evaluación docente creada exitosamente.',
                'data' => $evaluacion,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la evaluación docente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Ver evaluaciones de un usuario
    public function verEvaluacionDocente($user_id)
    {
        try {
            // Obtener las evaluacione del usuario
            $evaluaciones = EvaluacionDocente::where('user_id', $user_id)->first();

            if ($evaluaciones->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraro la evaluacion para este usuario.',
                ], 404);
            }

            return response()->json([
                'data' => $evaluaciones,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener  la evaluacion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Actualizar una evaluación
    public function actualizarEvaluacionDocente(ActualizarEvaluacionDocenteRequest $request)
    {
        try {
            $evaluacion = DB::transaction(function () use ($request) {
                $user = $request->user();
                $evaluacion = EvaluacionDocente::where('user_id', $user->id)->first();
                $datosEvaluacionActualizar = $request->validated();
                $evaluacion->update($datosEvaluacionActualizar);

                return $evaluacion;

            });

            return response()->json([
                'message' => 'Evaluación actualizada exitosamente.',
                'data' => $evaluacion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la evaluación.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
