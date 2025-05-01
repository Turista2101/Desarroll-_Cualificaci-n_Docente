<?php

namespace App\Http\Controllers\Aspirante;

use App\Http\Requests\RequestAspirante\RequestRut\ActualizarRutRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Rut;
use App\Services\ArchivoService;
use App\Http\Requests\RequestAspirante\RequestRut\CrearRutRequest;
use Illuminate\Support\Facades\DB;


class RutController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    public function crearRut(CrearRutRequest $request)
    {
        try {
            $usuarioId = $request->user()->id;
            
            $rutExistente = Rut::where('user_id', $usuarioId)->first();

            if ($rutExistente) {
                return response()->json([
                    'message' => 'Ya tienes un RUT registrado. No puedes crear otro.',
                ], 409);
            }
            
            $rut = DB::transaction(function () use ($request) {
                $datos = $request->validated();
                $datos['user_id'] = $request->user()->id;

                $rut = Rut::create($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $rut, 'Rut');
                }

                return $rut;
            });

            return response()->json([
                'message' => 'RUT creado exitosamente',
                'data' => $rut,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el RUT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerRut(Request $request)
    {
        try {
            $rut = Rut::where('user_id', $request->user()->id)
                ->with(['documentosRut:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();

            foreach ($rut->documentosRut as $documento) {
                $documento->archivo_url = asset('storage/' . $documento->archivo);
            }

            return response()->json(['rut' => $rut], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el RUT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function actualizarRut(ActualizarRutRequest $request)
    {
        try {
            $rut = DB::transaction(function () use ($request) {
                $rut = Rut::where('user_id', $request->user()->id)->firstOrFail();

                $rut->update($request->validated());

                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $rut, 'Rut');
                }

                return $rut;
            });

            return response()->json([
                'message' => 'RUT actualizado exitosamente',
                'data' => $rut->fresh(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el RUT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarRut(Request $request)
    {
        try {
            $rut = Rut::where('user_id', $request->user()->id)->firstOrFail();

            $this->archivoService->eliminarArchivoDocumento($rut);
            $rut->delete();

            return response()->json(['message' => 'RUT eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el RUT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
