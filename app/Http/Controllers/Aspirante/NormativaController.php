<?php
// Define el namespace donde se encuentra este controlador

namespace App\Http\Controllers\Aspirante;
// Importa el modelo Normativa para interactuar con la base de datos
use App\Models\Aspirante\Normativa;
// Importa el servicio encargado de gestionar archivos
use App\Services\ArchivoService;

// Importa herramientas para manejar transacciones y peticiones
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// Importa las clases de validación personalizadas para crear y actualizar normativa
use App\Http\Requests\RequestNormativa\CrearNormativaRequest;
use App\Http\Requests\RequestNormativa\ActualizarNormativaRequest;

// Define la clase del controlador NormativaController

class NormativaController
{
    // Propiedad protegida para usar el servicio de archivos
    protected $archivoService;

    // Constructor que recibe e inyecta el servicio de archivos
    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

     // Método para crear una nueva normativa
    public function crearNormativa(CrearNormativaRequest $request)
    {
        try {
            // Inicia una transacción para asegurar integridad en la base de datos
            $normativa = DB::transaction(function () use ($request) {
                 // Obtiene los datos validados del request
                $datos = $request->validated(); 
                // Asigna el ID del usuario autenticado al campo user_id
                $datos['user_id'] = $request->user()->id;
                // Crea la normativa en la base de datos
                $normativa = Normativa::create($datos);
                // Si el request contiene un archivo, se guarda mediante el servicio
                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $normativa, 'Normativas');
                }
                // Devuelve la normativa recién creada
                return $normativa;
            });
            // Devuelve respuesta de éxito con los datos creados
            return response()->json([
                'message' => 'Normativa y documento guardados correctamente',
                'data' => $normativa,
            ], 201);
        } catch (\Exception $e) {
        // En caso de error, devuelve mensaje de fallo y el detalle del error
            return response()->json([
                'message' => 'Error al crear la normativa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Método para obtener todas las normativas
    public function obtenerNormativas()
    {
        try {
            // Consulta todas las normativas incluyendo los documentos asociados
            $normativas = Normativa::with(['documentosNormativa:id_documento,documentable_id,archivo,estado'])
                ->orderBy('created_at')
                ->get();

            // Verifica si hay normativas registradas
            if ($normativas->isEmpty()) {
                throw new \Exception('No se encontraron normativas', 404);
            }

            // Agrega URL completa del archivo a cada documento
            $normativas->each(function ($normativa) {
                $normativa->documentosNormativa->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });

            // Devuelve las normativas encontradas
            return response()->json(['normativas' => $normativas], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'message' => 'Error al obtener las normativas.',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Obtener una normativa por ID
    public function obtenerNormativaPorId($id)
    {
        try {
            // Busca la normativa por ID
            $normativa = Normativa::where('id_normativa', $id)
                ->with(['documentosNormativa:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();

            // Agrega URL del archivo si existe
            $normativa->documentosNormativa->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });

            // Devuelve la normativa encontrada
            return response()->json(['normativa' => $normativa], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            $codigo = (is_int($e->getCode()) && $e->getCode() !== 0) ? $e->getCode() : 500;

            return response()->json([
                'message' => 'Error al obtener la normativa',
                'error'   => $e->getMessage()
            ], $codigo);
        }
    }
    // Actualizar una normativa
    public function actualizarNormativa(ActualizarNormativaRequest $request, $id)
    {
        try {
            // Inicia transacción para la operación de actualización
            $normativa = DB::transaction(function () use ($request, $id) {
            // Busca la normativa a actualizar
                $normativa = Normativa::findOrFail($id);
                // Obtiene los datos validados del request
                $datos = $request->validated();
                // Actualiza los campos de la normativa
                $normativa->update($datos);
                // Si hay un archivo nuevo, se actualiza mediante el servicio
                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $normativa, 'Normativas');
                }
                // Devuelve la normativa actualizada
                return $normativa;
            });
            // Devuelve respuesta de éxito con la normativa actualizada (recargada)
            return response()->json([
                'mensaje' => 'Normativa actualizada correctamente',
                'data' => $normativa->fresh(),
            ], 200);
        } catch (\Exception $e) {
            // Manejo de errores en la actualización
            return response()->json([
                'message' => 'Error al actualizar la normativa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Eliminar una normativa
    public function eliminarNormativa( $id)
    {
        try {
            // Busca la normativa por su ID, lanza error si no existe
            $normativa = Normativa::where('id_normativa', $id)
                ->firstOrFail();
            // Realiza la eliminación dentro de una transacción
            DB::transaction(function () use ($normativa) {
            // Elimina el archivo relacionado mediante el servicio
                $this->archivoService->eliminarArchivoDocumento($normativa);
            // Elimina la normativa de la base de datos
                $normativa->delete();
            });
            // Devuelve mensaje de éxito
            return response()->json(['mensaje' => 'Normativa eliminada correctamente'], 200);
        } catch (\Exception $e) {
            // Manejo de errores en la eliminación
            return response()->json([
                'message' => 'Error al eliminar la normativa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
