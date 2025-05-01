<?php
// Se define el namespace para organizar el código en carpetas y evitar conflictos de nombres.

namespace App\Http\Controllers\Aspirante;

// Importación de clases necesarias para el funcionamiento del controlador.

use App\Http\Requests\RequestAspirante\RequestExperiencia\ActualizarExperienciaRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Experiencia;
use App\Services\ArchivoService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RequestAspirante\RequestExperiencia\CrearExperienciaRequest;
// Definición del controlador de experiencias.

class ExperienciaController
{
    // Se declara una propiedad para usar el servicio de archivos.

    protected $archivoService;

        // Constructor que recibe el servicio de archivos y lo asigna a la propiedad del controlador.

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Método para crear una nueva experiencia.
    public function crearExperiencia(CrearExperienciaRequest $request)
    {
        try {
            // Se ejecuta una transacción para asegurar que todos los cambios se hagan correctamente.

            $experiencia = DB::transaction(function () use ($request) {
                $datos = $request->validated();
                // Se asigna el ID del usuario autenticado.

                $datos['user_id'] = $request->user()->id;
                // Se crea el registro en la base de datos.

                $experiencia = Experiencia::create($datos);
                // Si se sube un archivo, se guarda con el servicio correspondiente.

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $experiencia, 'Experiencias');
                }

                return $experiencia;
            });
            // Se retorna una respuesta exitosa con los datos de la experiencia creada.

            return response()->json([
                'message' => 'Experiencia creada exitosamente',
                'data' => $experiencia
            ], 201);
        } catch (\Exception $e) {
            // En caso de error, se retorna un mensaje con el detalle.
            return response()->json([
                'message' => 'Error al crear la experiencia.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Método para obtener todas las experiencias del usuario autenticado.

    public function obtenerExperiencias(Request $request)
    {
        try {
            // Se obtiene el usuario autenticado.

            $user = $request->user();
            // Si no hay usuario, se lanza una excepción.

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            // Se consultan las experiencias del usuario, incluyendo los documentos asociados.

            $experiencias = Experiencia::where('user_id', $user->id)
                ->with(['documentosExperiencia:id_documento,documentable_id,archivo,estado'])
                ->orderBy('created_at')
                ->get();
            // Si no hay experiencias, se lanza una excepción.

            if ($experiencias->isEmpty()) {
                throw new \Exception('No se encontraron experiencias', 404);
            }
            // Se recorre cada experiencia para agregar la URL del archivo si existe.

            $experiencias->each(function ($experiencia) {
                $experiencia->documentosExperiencia->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });
            // Se retorna la lista de experiencias.

            return response()->json(['experiencias' => $experiencias], 200);
        } catch (\Exception $e) {
            // En caso de error, se retorna una respuesta con el mensaje.

            return response()->json([
                'message' => 'Error al obtener las experiencias.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Método para obtener una experiencia por su ID.
    public function obtenerExperienciaPorId(Request $request, $id)
    {
        try {
            // Se obtiene el usuario autenticado.

            $user = $request->user();
            // Si no hay usuario, se lanza una excepción.

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            // Se busca la experiencia por ID y usuario.

            $experiencia = Experiencia::where('id_experiencia', $id)
                ->where('user_id', $user->id)
                ->with(['documentosExperiencia:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();
            // Se agrega la URL de los archivos si existen.

            $experiencia->documentosExperiencia->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });
            // Se retorna la experiencia encontrada.

            return response()->json(['experiencia' => $experiencia], 200);
        } catch (\Exception $e) {
            // En caso de error, se retorna un mensaje detallado.

            return response()->json([
                'message' => 'Error al obtener la experiencia.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Método para actualizar una experiencia existente.
    public function actualizarExperiencia(ActualizarExperienciaRequest $request, $id)
    {
        try {
            // Se ejecuta una transacción para asegurar la integridad de los datos.

            $experiencia = DB::transaction(function () use ($request, $id) {
            // Se obtiene el usuario autenticado.

                $user = $request->user();
                // Se busca la experiencia por ID y usuario.

                $experiencia = Experiencia::where('id_experiencia', $id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();
                // Se validan los datos enviados en la solicitud.

                $datos = $request->validated();
                // Se actualiza la experiencia con los nuevos datos.

                $experiencia->update($datos);
                // Si hay un nuevo archivo, se actualiza el documento.

                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $experiencia, 'Experiencias');
                }

                return $experiencia;
            });
            // Se retorna la experiencia actualizada y un mensaje de éxito.

            return response()->json([
                'message' => 'Experiencia actualizada correctamente',
                'data' => $experiencia->fresh()
            ], 200);
        } catch (\Exception $e) {
            // En caso de error, se retorna el mensaje correspondiente.

            return response()->json([
                'message' => 'Error al actualizar la experiencia.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar un registro de experiencia
    public function eliminarExperiencia(Request $request, $id)
    {
        try {
            // Obtener el usuario autenticado desde la solicitud.

            $user = $request->user();
            // Buscar la experiencia por su ID y asegurarse de que pertenezca al usuario autenticado.
            // Si no se encuentra, lanza una excepción automáticamente (firstOrFail).
      
            $experiencia = Experiencia::where('id_experiencia', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        // Iniciar una transacción para garantizar que las operaciones se realicen de manera atómica.
            DB::transaction(function () use ($experiencia) {
            // Eliminar el archivo asociado a la experiencia utilizando el servicio de archivos.
                $this->archivoService->eliminarArchivoDocumento($experiencia);
            // Eliminar el registro de la experiencia de la base de datos.
                $experiencia->delete();
            });
        // Retornar una respuesta JSON indicando que la experiencia fue eliminada correctamente.
            return response()->json(['message' => 'Experiencia eliminada correctamente'], 200);
        } catch (\Exception $e) {
        // Manejo de errores: retornar una respuesta JSON con el mensaje de error y el código 500.
            return response()->json([
                'message' => 'Error al eliminar la experiencia.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
