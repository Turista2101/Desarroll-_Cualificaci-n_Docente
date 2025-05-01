<?php

namespace App\Http\Controllers\Aspirante;

use App\Http\Requests\RequestAspirante\RequestEps\ActualizarEpsRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Eps;
use App\Services\ArchivoService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RequestAspirante\RequestEps\CrearEpsRequest;



class EpsController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Crear un registro de EPS
    public function crearEps(CrearEpsRequest $request)
    {
        try {
            $usuarioId = $request->user()->id;

            // Verificar si el usuario ya tiene una EPS registrada
            $epsExistente = Eps::where('user_id', $usuarioId)->first();

            if ($epsExistente) {
                return response()->json([
                    'message' => 'Ya tienes una EPS registrada. No puedes crear otra.',
                ], 409);
            }

            $eps = DB::transaction(function () use ($request) {
                $datos = $request->validated();
                $datos['user_id'] = $request->user()->id;

                $eps = Eps::create($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $eps, 'Eps');
                }

                return $eps;
            });

            return response()->json([
                'message' => 'EPS y documento creado exitosamente',
                'data'    => $eps
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la EPS o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Obtener la información de EPS del usuario autenticado
    public function obtenerEps(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $eps = Eps::where('user_id', $user->id)
                ->with(['documentosEps:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();

            foreach ($eps->documentosEps as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }

            return response()->json(['eps' => $eps], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la información de EPS',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Actualizar EPS
    public function actualizarEps(ActualizarEpsRequest $request)
    {
        try {
            $eps = DB::transaction(function () use ($request) {
                $user = $request->user();

                $eps = Eps::where('user_id', $user->id)->firstOrFail();

                $datos = $request->validated();
                $eps->update($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $eps, 'Eps');
                }

                return $eps;
            });

            return response()->json([
                'message' => 'EPS actualizado exitosamente',
                'data'    => $eps->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el EPS',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
