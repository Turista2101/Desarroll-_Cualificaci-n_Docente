<?php
// Define el espacio de nombres del controlador
namespace App\Http\Controllers\Aspirante;

// Importación de clases necesarias
use App\Http\Requests\RequestAspirante\RequestEstudio\ActualizarEstudioRequest;
use App\Http\Requests\RequestAspirante\RequestEstudio\CrearEstudioRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Estudio;
use App\Services\ArchivoService;
use Illuminate\Support\Facades\DB;
// Define el controlador de EstudioController
class EstudioController
{
    // Servicio para manejar archivos
    protected $archivoService;
      // Constructor que inyecta el servicio de archivos
    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Crear un registro de estudio
    public function crearEstudio(CrearEstudioRequest $request)
    {
        try {
            // Ejecuta dentro de una transacción para asegurar integridad de datos
            $estudio = DB::transaction(function () use ($request) {
                $datos = $request->validated();
                // Asigna el ID del usuario autenticado al estudio 
                $datos['user_id'] = $request->user()->id;
                 // Crea el registro del estudio
                $estudio = Estudio::create($datos);
                 // Verifica si se adjuntó un archivo y lo guarda
                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $estudio, 'Estudios');
                }

                return $estudio;
            });
            // Retorna respuesta exitosa

            return response()->json([
                'message' => 'Estudio y documento creados exitosamente',
                'data'    => $estudio,
            ], 201);
        } catch (\Exception $e) {
              // Manejo de errores
            return response()->json([
                'message' => 'Error al crear el estudio o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Método para obtener los estudios del usuario autenticado
    public function obtenerEstudios(Request $request)
    {
        try {
            $user = $request->user();// Obtiene el usuario autenticado

            // Verifica si el usuario está autenticado

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            // Consulta los estudios del usuario con sus documentos
            $estudios = Estudio::where('user_id', $user->id)
                ->with(['documentosEstudio:id_documento,documentable_id,archivo,estado'])
                ->orderBy('created_at')
                ->get();
            // Verifica si hay estudios registrados
            if ($estudios->isEmpty()) {
                return response()->json([
                    'mesaje'=>'No se encontraron estudios',
                    'estudios'=> null
                ], 200);
            }
             // Agrega URL completa del archivo a cada documento
            $estudios->each(function ($estudio) {
                $estudio->documentosEstudio->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });
 // Devuelve los estudios encontrados
            return response()->json(['estudios' => $estudios], 200);
        } catch (\Exception $e) {
             // Manejo de errores
            return response()->json([
                'message' => 'Error al obtener los estudios',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

     // Método para obtener un estudio específico por ID
    public function obtenerEstudioPorId(Request $request, $id)
    {
        try {
            $user = $request->user();// Obtiene el usuario autenticado

            // Verifica autenticación

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            // Busca el estudio por ID y por usuario
            $estudio = Estudio::where('id_estudio', $id)
                ->where('user_id', $user->id)
                ->with(['documentosEstudio:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();
              // Agrega URL del archivo si existe
            $estudio->documentosEstudio->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });
             // Devuelve el estudio encontrado
            return response()->json(['estudio' => $estudio], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'message' => 'Error al obtener el estudio',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Método para actualizar un estudio existente
    public function actualizarEstudio(ActualizarEstudioRequest $request, $id)
    {
        try {
            $estudio = DB::transaction(function () use ($request, $id) {
                $user = $request->user();// Usuario autenticado

                // Busca el estudio del usuario

                $estudio = Estudio::where('id_estudio', $id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();
                // Valida y obtiene los datos
                $datos = $request->validated();
                // Actualiza los datos del estudio
                $estudio->update($datos);
                 // Si se adjuntó nuevo archivo, lo actualiza
                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $estudio, 'Estudios');
                }

                return $estudio;
            });
             // Respuesta exitosa con datos actualizados
            return response()->json([
                'message' => 'Estudio actualizado correctamente',
                'data'    => $estudio->fresh()
            ], 200);
        } catch (\Exception $e) {
              // Manejo de errores
            return response()->json([
                'message' => 'Error al actualizar el estudio',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

     // Método para eliminar un estudio
    public function eliminarEstudio(Request $request, $id)
    {
        try {
            $user = $request->user();// Usuario autenticado

            // Busca el estudio del usuario

            $estudio = Estudio::where('id_estudio', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            // Elimina el estudio y sus archivos dentro de una transacción
            DB::transaction(function () use ($estudio) {
                $this->archivoService->eliminarArchivoDocumento($estudio);
                $estudio->delete();
            });
             // Respuesta exitosa
            return response()->json(['message' => 'Estudio eliminado correctamente'], 200);
        } catch (\Exception $e) {
             // Manejo de errores
            return response()->json([
                'message' => 'Error al eliminar el estudio',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
