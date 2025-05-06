<?php
    // Indica que el archivo es código PHP
namespace App\Http\Controllers\Docente;
// Define el espacio de nombres donde está ubicado este controlador.
// Es útil para organizar mejor el código y evitar conflictos entre clases con el mismo nombre.
use App\Http\Requests\RequestDocente\RequestEvaluacionDocente\ActualizarEvaluacionDocenteRequest;
use App\Http\Requests\RequestDocente\RequestEvaluacionDocente\CrearEvaluacionDocenteRequest;
// Importa las clases que validan los datos entrantes para crear o actualizar evaluaciones docentes.
use App\Models\Docente\EvaluacionDocente;
// Importa el modelo EvaluacionDocente para interactuar con la tabla de evaluaciones docentes.

use Illuminate\Support\Facades\DB;
// Importa la fachada DB para ejecutar transacciones y consultas a la base de datos.


class EvaluacionDocenteController
// Declara la clase EvaluacionDocenteController.
// Esta clase contendrá los métodos que manejarán las solicitudes HTTP relacionadas con evaluaciones docentes.
{
    // Crear una evaluación
    public function crearEvaluacionDocente(CrearEvaluacionDocenteRequest $request)
     // Método público para crear una evaluación docente.
    // Recibe un request que ya ha sido validado por CrearEvaluacionDocenteRequest.
    {
        try {
            // Intenta ejecutar el bloque de código. Si hay un error, pasa al catch.
            $usuarioId = $request->user()->id;

            // Verificar si ya tiene una evaluación docente
            $evaluacionExistente = EvaluacionDocente::where('user_id', $usuarioId)->first();
            // Consulta si ya existe una evaluación registrada para ese usuario.

            if ($evaluacionExistente) {
          // Si ya existe, devuelve una respuesta con código 409 (conflicto) e impide crear otra.

                return response()->json([
                    'message' => 'Ya tienes una evaluación docente registrada. No puedes crear otra.',
                ], 409);
            }
            $evaluacion = DB::transaction(function () use ($request) {
             // Inicia una transacción de base de datos para asegurar que todo se ejecute correctamente.     
                $datosEvaluacion = $request->validated();
                $datosEvaluacion['user_id'] = $request->user()->id;

                // Asignar automáticamente estado 'pendiente'
                $datosEvaluacion['estado_evaluacion_docente'] = 'Pendiente';
    
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
