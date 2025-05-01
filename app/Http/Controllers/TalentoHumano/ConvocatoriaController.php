<?php

namespace App\Http\Controllers\TalentoHumano;


use App\Http\Requests\RequestTalentoHumano\RequestConvocatoria\ActualizarConvocatoriaRequest;
use App\Http\Requests\RequestTalentoHumano\RequestConvocatoria\CrearConvocatoriaRequest;
use App\Models\Aspirante\Documento;
use App\Models\TalentoHumano\Convocatoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Notifications\NotificacionGeneral;
use Illuminate\Support\Facades\Notification;
use App\Models\Usuario\User;
use App\Services\ArchivoService;


class ConvocatoriaController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Crear un registro de convocatoria
    public function crearConvocatoria(CrearConvocatoriaRequest $request)
    {
        try {
            $convocatoria = DB::transaction(function () use ($request) {
                $datosConvocatoria = $request->validated();
                $convocatoria = Convocatoria::create($datosConvocatoria);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $convocatoria, 'Convocatorias');
                }

                return $convocatoria;
            });

            return response()->json([
                'mensaje' => 'Convocatoria creada exitosamente',
                'data' => $convocatoria
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear la convocatoria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar un registro de convocatoria
    public function actualizarConvocatoria(ActualizarConvocatoriaRequest $request, $id)
    {
        try {
            $convocatoria = DB::transaction(function () use ($request, $id) {
                $convocatoria = Convocatoria::findOrFail($id);

                $convocatoria->update($request->validated());

                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $convocatoria, 'Convocatorias');
                }

                return $convocatoria;
            });

            return response()->json([
                'mensaje' => 'Convocatoria actualizada exitosamente',
                'data' => $convocatoria->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar la convocatoria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar una convocatoria
    public function eliminarConvocatoria($id)
    {
        try {
            $convocatoria = Convocatoria::findOrFail($id);

            DB::transaction(function () use ($convocatoria) {
                $this->archivoService->eliminarArchivoDocumento($convocatoria);
                $convocatoria->delete();
            });

            return response()->json(['mensaje' => 'Convocatoria eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar la convocatoria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener todas las convocatorias
    public function obtenerConvocatorias()
    {
        try {
            $convocatorias = Convocatoria::with('documentosConvocatoria')->orderBy('created_at', 'desc')->get();

            if ($convocatorias->isEmpty()) {
                throw new \Exception('No se encontraron convocatorias', 404);
            }

            foreach ($convocatorias as $convocatoria) {
                foreach ($convocatoria->documentosConvocatoria as $documento) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }

            return response()->json(['convocatorias' => $convocatorias], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener las convocatorias',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Obtener una convocatoria por su ID
    public function obtenerConvocatoriaPorId($id)
    {
        try {
            $convocatoria = Convocatoria::with('documentosConvocatoria')->findOrFail($id);

            foreach ($convocatoria->documentosConvocatoria as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }

            return response()->json(['convocatoria' => $convocatoria], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener la convocatoria',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
