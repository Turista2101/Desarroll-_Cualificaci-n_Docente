<?php

namespace App\Http\Controllers\Aspirante;

use App\Http\Requests\RequestAspirante\RequestAptitud\ActualizarAptitudRequest;
use App\Http\Requests\RequestAspirante\RequestAptitud\CrearAptitudRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Aptitud;

class AptitudController
{
    public function crearAptitud(CrearAptitudRequest $request)
    {
        try {
            // Obtener los datos validados de la solicitud
            $datosAptitudCrear = $request->validated();

            // Asignar el ID del usuario autenticado
            $datosAptitudCrear['user_id'] = $request->user()->id;

            // Crear la nueva aptitud
            $aptitud = Aptitud::create($datosAptitudCrear);

            // Retornar respuesta con la aptitud creada
            return response()->json(['aptitud' => $aptitud], 201);
            
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'mensaje' => 'Error al crear la aptitud.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Obtener todas las aptitudes del usuario autenticado
    public function obtenerAptitudes(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $aptitudes = Aptitud::where('user_id', $user->id)->get();

            return response()->json(['aptitudes' => $aptitudes], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener las aptitudes.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }


    // Obtener una aptitud por su ID (solo si pertenece al usuario)
    public function obtenerAptitudesPorId(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $aptitud = Aptitud::where('user_id', $user->id)
                              ->where('id_aptitud', $id)
                              ->first();

            if (!$aptitud) {
                return response()->json(['mensaje' => 'Aptitud no encontrada.'], 404);
            }

            return response()->json(['aptitud' => $aptitud], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener la aptitud.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Actualizar una aptitud por su ID (solo si pertenece al usuario)
    public function actualizarAptitudPorId(ActualizarAptitudRequest $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $aptitud = Aptitud::where('user_id', $user->id)
                                ->where('id_aptitud', $id)
                                ->first();

            if (!$aptitud) {
                return response()->json(['mensaje' => 'Aptitud no encontrada.'], 404);
            }

            // Obtener los datos validados de la solicitud
            $datosAptitudActualizar = $request->validated();

            $aptitud->update($datosAptitudActualizar);

            return response()->json([
                'mensaje' => 'Aptitud actualizada correctamente.',
                'aptitud' => $aptitud
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar la aptitud.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Eliminar una aptitud por su ID (solo si pertenece al usuario)
    public function eliminarAptitudPorId(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $aptitud = Aptitud::where('user_id', $user->id)
                              ->where('id_aptitud', $id)
                              ->first();

            if (!$aptitud) {
                return response()->json(['mensaje' => 'Aptitud no encontrada.'], 404);
            }

            $aptitud->delete();

            return response()->json(['mensaje' => 'Aptitud eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar la aptitud.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
    
