<?php
// Definimos el namespace del controlador
namespace App\Http\Controllers\Aspirante;
// Importamos los requests personalizados para validar los datos al crear o actualizar aptitudes
use App\Http\Requests\RequestAspirante\RequestAptitud\ActualizarAptitudRequest;
use App\Http\Requests\RequestAspirante\RequestAptitud\CrearAptitudRequest;

// Importamos la clase Request de Laravel
use Illuminate\Http\Request;
// Importamos el modelo Aptitud
use App\Models\Aspirante\Aptitud;

// Definimos la clase del controlador
class AptitudController
{
      // Método para crear una nueva aptitud
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
            ], $e->getCode() ?: 500);// Retorna el código del error o 500 si no hay código
        }
    }

    // Método para obtener todas las aptitudes del usuario autenticado
    public function obtenerAptitudes(Request $request)
    {
        try {
             // Obtener usuario autenticado desde el request
            $user = $request->user();
            // Verificar que el usuario esté autenticado
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            
            // Obtener todas las aptitudes del usuario
            $aptitudes = Aptitud::where('user_id', $user->id)->get();
            // Retornar respuesta con las aptitudes encontradas
            return response()->json(['aptitudes' => $aptitudes], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'mensaje' => 'Error al obtener las aptitudes.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

// Método para obtener una aptitud por su ID (solo si pertenece al usuario autenticado)
    public function obtenerAptitudesPorId(Request $request, $id)
    {
        try {
             // Obtener usuario autenticado
            $user = $request->user();
             // Verificar autenticación
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
             // Buscar la aptitud por ID y que pertenezca al usuario
            $aptitud = Aptitud::where('user_id', $user->id)
                              ->where('id_aptitud', $id)
                              ->first();
             // Si no se encuentra la aptitud, retornar mensaje 404
            if (!$aptitud) {
                return response()->json(['mensaje' => 'Aptitud no encontrada.'], 404);
            }
            // Retornar aptitud encontrada
            return response()->json(['aptitud' => $aptitud], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'mensaje' => 'Error al obtener la aptitud.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Método para actualizar una aptitud por su ID (solo si pertenece al usuario)
    public function actualizarAptitudPorId(ActualizarAptitudRequest $request, $id)
    {
        try {
             // Obtener usuario autenticado
            $user = $request->user();
            // Verificar autenticación
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            // Buscar la aptitud por ID y usuario
            $aptitud = Aptitud::where('user_id', $user->id)
                                ->where('id_aptitud', $id)
                                ->first();
            // Si no se encuentra, retornar error 404
            if (!$aptitud) {
                return response()->json(['mensaje' => 'Aptitud no encontrada.'], 404);
            }

            // Obtener los datos validados de la solicitud
            $datosAptitudActualizar = $request->validated();
            // Actualizar la aptitud con los nuevos datos
            $aptitud->update($datosAptitudActualizar);
            // Retornar mensaje de éxito
            return response()->json([
                'mensaje' => 'Aptitud actualizada correctamente.',
                'aptitud' => $aptitud
            ], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'mensaje' => 'Error al actualizar la aptitud.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

     // Método para eliminar una aptitud por su ID (solo si pertenece al usuario)
    public function eliminarAptitudPorId(Request $request, $id)
    {
        try {
             // Obtener usuario autenticado
            $user = $request->user();
            // Verificar autenticación
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            // Buscar la aptitud por ID y usuario
            $aptitud = Aptitud::where('user_id', $user->id)
                              ->where('id_aptitud', $id)
                              ->first();
             // Si no se encuentra, retornar mensaje 404
            if (!$aptitud) {
                return response()->json(['mensaje' => 'Aptitud no encontrada.'], 404);
            }
            // Eliminar la aptitud
            $aptitud->delete();
             // Retornar mensaje de éxito
            return response()->json(['mensaje' => 'Aptitud eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'mensaje' => 'Error al eliminar la aptitud.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
    
