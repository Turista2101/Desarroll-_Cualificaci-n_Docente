<?php

namespace App\Http\Controllers\Aspirante;

use App\Models\Aspirante\Normativa;
use App\Services\ArchivoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\RequestNormativa\CrearNormativaRequest;
use App\Http\Requests\RequestNormativa\ActualizarNormativaRequest;


class NormativaController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Crear una nueva normativa
    public function crearNormativa(CrearNormativaRequest $request)
    {
        try {
            $normativa = DB::transaction(function () use ($request) {
                $datos = $request->validated(); // Datos ya validados por CrearNormativaRequest
                $datos['user_id'] = $request->user()->id;

                $normativa = Normativa::create($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $normativa, 'Normativas');
                }

                return $normativa;
            });

            return response()->json([
                'message' => 'Normativa y documento guardados correctamente',
                'data' => $normativa,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la normativa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Obtener todas las normativas
    public function obtenerNormativas()
    {
        try {
            // Consulta todas las normativas con sus documentos
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
            $normativa = DB::transaction(function () use ($request, $id) {
                $user = $request->user();

                $normativa = Normativa::where('id_normativa', $id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $datos = $request->validated();
                $normativa->update($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $normativa, 'Normativas');
                }

                return $normativa;
            });

            return response()->json([
                'mensaje' => 'Normativa actualizada correctamente',
                'data' => $normativa->fresh(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la normativa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Eliminar una normativa
    public function eliminarNormativa(Request $request, $id)
    {
        try {
            $user = $request->user();

            $normativa = Normativa::where('id_normativa', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            DB::transaction(function () use ($normativa) {
                $this->archivoService->eliminarArchivoDocumento($normativa);
                $normativa->delete();
            });

            return response()->json(['mensaje' => 'Normativa eliminada correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la normativa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
