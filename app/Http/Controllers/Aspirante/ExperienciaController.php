<?php

namespace App\Http\Controllers\Aspirante;


use App\Http\Requests\RequestAspirante\RequestExperiencia\ActualizarExperienciaRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Experiencia;
use App\Services\ArchivoService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RequestAspirante\RequestExperiencia\CrearExperienciaRequest;

class ExperienciaController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Crear un registro de experiencia
    public function crearExperiencia(CrearExperienciaRequest $request)
    {
        try {
            $experiencia = DB::transaction(function () use ($request) {
                $datos = $request->validated();
                $datos['user_id'] = $request->user()->id;

                $experiencia = Experiencia::create($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $experiencia, 'Experiencias');
                }

                return $experiencia;
            });

            return response()->json([
                'message' => 'Experiencia creada exitosamente',
                'data' => $experiencia
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la experiencia.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener todos los registros de experiencia
    public function obtenerExperiencias(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $experiencias = Experiencia::where('user_id', $user->id)
                ->with(['documentosExperiencia:id_documento,documentable_id,archivo,estado'])
                ->orderBy('created_at')
                ->get();

            if ($experiencias->isEmpty()) {
                throw new \Exception('No se encontraron experiencias', 404);
            }

            $experiencias->each(function ($experiencia) {
                $experiencia->documentosExperiencia->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });

            return response()->json(['experiencias' => $experiencias], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las experiencias.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Obtener un registro de experiencia por ID
    public function obtenerExperienciaPorId(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $experiencia = Experiencia::where('id_experiencia', $id)
                ->where('user_id', $user->id)
                ->with(['documentosExperiencia:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();

            $experiencia->documentosExperiencia->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });

            return response()->json(['experiencia' => $experiencia], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la experiencia.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Actualizar un registro de experiencia
    public function actualizarExperiencia(ActualizarExperienciaRequest $request, $id)
    {
        try {
            $experiencia = DB::transaction(function () use ($request, $id) {
                $user = $request->user();

                $experiencia = Experiencia::where('id_experiencia', $id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $datos = $request->validated();
                $experiencia->update($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $experiencia, 'Experiencias');
                }

                return $experiencia;
            });

            return response()->json([
                'message' => 'Experiencia actualizada correctamente',
                'data' => $experiencia->fresh()
            ], 200);
        } catch (\Exception $e) {
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
            $user = $request->user();

            $experiencia = Experiencia::where('id_experiencia', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            DB::transaction(function () use ($experiencia) {
                $this->archivoService->eliminarArchivoDocumento($experiencia);
                $experiencia->delete();
            });

            return response()->json(['message' => 'Experiencia eliminada correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la experiencia.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
