<?php

namespace App\Http\Controllers\Aspirante;


use App\Http\Requests\RequestAspirante\RequestEstudio\ActualizarEstudioRequest;
use App\Http\Requests\RequestAspirante\RequestEstudio\CrearEstudioRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Estudio;
use App\Services\ArchivoService;
use Illuminate\Support\Facades\DB;

class EstudioController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Crear un registro de estudio
    public function crearEstudio(CrearEstudioRequest $request)
    {
        try {
            $estudio = DB::transaction(function () use ($request) {
                $datos = $request->validated();
                $datos['user_id'] = $request->user()->id;

                $estudio = Estudio::create($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $estudio, 'Estudios');
                }

                return $estudio;
            });

            return response()->json([
                'message' => 'Estudio y documento creados exitosamente',
                'data'    => $estudio,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el estudio o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Obtener estudios del usuario autenticado
    public function obtenerEstudios(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $estudios = Estudio::where('user_id', $user->id)
                ->with(['documentosEstudio:id_documento,documentable_id,archivo,estado'])
                ->orderBy('created_at')
                ->get();

            if ($estudios->isEmpty()) {
                throw new \Exception('No se encontraron estudios', 404);
            }

            $estudios->each(function ($estudio) {
                $estudio->documentosEstudio->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });

            return response()->json(['estudios' => $estudios], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los estudios',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Obtener un estudio por ID
    public function obtenerEstudioPorId(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $estudio = Estudio::where('id_estudio', $id)
                ->where('user_id', $user->id)
                ->with(['documentosEstudio:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();

            $estudio->documentosEstudio->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });

            return response()->json(['estudio' => $estudio], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el estudio',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Actualizar estudio
    public function actualizarEstudio(ActualizarEstudioRequest $request, $id)
    {
        try {
            $estudio = DB::transaction(function () use ($request, $id) {
                $user = $request->user();

                $estudio = Estudio::where('id_estudio', $id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $datos = $request->validated();
                $estudio->update($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $estudio, 'Estudios');
                }

                return $estudio;
            });

            return response()->json([
                'message' => 'Estudio actualizado correctamente',
                'data'    => $estudio->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el estudio',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar estudio
    public function eliminarEstudio(Request $request, $id)
    {
        try {
            $user = $request->user();

            $estudio = Estudio::where('id_estudio', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            DB::transaction(function () use ($estudio) {
                $this->archivoService->eliminarArchivoDocumento($estudio);
                $estudio->delete();
            });

            return response()->json(['message' => 'Estudio eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el estudio',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
