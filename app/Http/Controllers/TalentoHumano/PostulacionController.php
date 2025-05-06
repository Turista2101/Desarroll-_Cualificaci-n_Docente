<?php

namespace App\Http\Controllers\TalentoHumano;
// Importaciones necesarias para usar constantes, modelos y servicios
use App\Constants\ConstTalentoHumano\EstadoPostulacion;
use App\Models\TalentoHumano\Postulacion;
use App\Models\TalentoHumano\Convocatoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\GeneradorHojaDeVidaPDFService;


class PostulacionController
{
    // Servicio para generar PDF de hoja de vida
    protected $generadorHojaDeVidaPDFService;

    public function __construct(GeneradorHojaDeVidaPDFService $generadorHojaDeVidaPDFService)
    {
        $this->generadorHojaDeVidaPDFService = $generadorHojaDeVidaPDFService;
    }
 /**
     * Crea una nueva postulación para una convocatoria.
     * Valida que la convocatoria no esté cerrada y que el usuario no se haya postulado previamente.
     */
    public function crearPostulacion(Request $request, $convocatoriaId)
    {
        try {
            $postulacion = DB::transaction(function () use ($request, $convocatoriaId) {
                $user = $request->user();

                // Buscar la convocatoria
                $convocatoria = Convocatoria::findOrFail($convocatoriaId);
                // Validar si está cerrada

                if ($convocatoria->estado_convocatoria === 'Cerrada') {
                    throw new \Exception('Esta convocatoria está cerrada y no admite más postulaciones.', 403);
                }

                // Verificar si ya está postulado
                $existe = Postulacion::where('user_id', $user->id)
                    ->where('convocatoria_id', $convocatoriaId)
                    ->exists();

                if ($existe) {
                    throw new \Exception('Ya te has postulado a esta convocatoria', 409);
                }

                // Crear postulación
                return Postulacion::create([
                    'user_id' => $user->id,
                    'convocatoria_id' => $convocatoriaId,
                    'estado_postulacion' => 'Enviada'
                ]);
            });

            return response()->json([
                'message' => 'Postulación enviada correctamente',
                'data' => $postulacion
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al crear la postulación.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
     /**
     * Obtiene todas las postulaciones con sus relaciones de usuario y convocatoria.
     */
    public function obtenerPostulaciones()
    {
        try {
            $postulaciones = Postulacion::with('usuarioPostulacion', 'convocatoriaPostulacion')->get();

            return response()->json(['postulaciones' => $postulaciones], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al obtener las postulaciones.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
/**
     * Obtiene las postulaciones hechas por el usuario autenticado.
     */
    public function obtenerPostulacionesUsuario(Request $request)
    {
        try {
            $postulaciones = Postulacion::where('user_id', $request->user()->id)
                ->with('convocatoriaPostulacion')
                ->get();

            return response()->json(['postulaciones' => $postulaciones], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al obtener las postulaciones del usuario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
 /**
     * Genera el PDF de la hoja de vida de un usuario para una convocatoria específica.
     */

    public function generarHojaDeVidaPDF($idConvocatoria, $idUsuario)
    {
        try {
            // Verifica si el usuario está postulado a la convocatoria
            $postulacion = Postulacion::where('convocatoria_id', $idConvocatoria)
                ->where('user_id', $idUsuario)
                ->first();

            if (!$postulacion) {
                return response()->json([
                    'message' => 'El usuario no está postulado a esta convocatoria.'
                ], 404);
            }

            // Llamas al servicio para generar el PDF
            return $this->generadorHojaDeVidaPDFService->generar($idUsuario);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al generar la hoja de vida.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
 /**
     * Actualiza el estado de una postulación específica.
     */

    public function actualizarEstadoPostulacion(Request $request, $idPostulacion)
    {
        try {
        // Validar que el estado sea válido
            $request->validate([
                'estado_postulacion' => 'required|in:' . implode(',', EstadoPostulacion::all()),
            ]);

            $postulacion = DB::transaction(function () use ($request, $idPostulacion) {
                $postulacion = Postulacion::find($idPostulacion);

                if (!$postulacion) {
                    throw new \Exception('Postulación no encontrada.', 404);
                }

                $postulacion->estado_postulacion = $request->estado_postulacion;
                $postulacion->save();

                // $talentoHumano = User::roles(['Docente', 'Aspirante'])->get();
                // Notification::send($talentoHumano, new NotificacionGeneral('Postulacion actualizada'));

                // $talentoHumano = User::role('Talento Humano')->get();
                // Notification::send($talentoHumano, new NotificacionGeneral('Postulacion actualizada'));

// Aquí se podrían enviar notificaciones si se desea
                return $postulacion;
            });

            return response()->json([
                'message' => 'Estado de postulación actualizado correctamente.',
                'postulacion' => $postulacion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el estado de la postulación.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }/**
     * Elimina una postulación por ID.
     */

    public function eliminarPostulacion($idPostulacion)
    {
        try {
            DB::transaction(function () use ($idPostulacion) {
                $postulacion = Postulacion::find($idPostulacion);

                if (!$postulacion) {
                    throw new \Exception('Postulación no encontrada.', 404);
                }

                $postulacion->delete();
            });

            return response()->json([
                'message' => 'Postulación eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al eliminar la postulación.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
 /**
     * Permite a un usuario eliminar su propia postulación.
     */
    public function eliminarPostulacionUsuario(Request $request, $id)
    {
        try {
            $postulacion = Postulacion::find($id);

            if (!$postulacion) {
                throw new \Exception('Postulación no encontrada.', 404);
            }

            if ($postulacion->user_id !== $request->user()->id) {
                throw new \Exception('No tienes permiso para eliminar esta postulación.', 403);
            }

            $postulacion->delete();

            return response()->json([
                'message' => 'Postulación eliminada correctamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al eliminar la postulación del usuario.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
