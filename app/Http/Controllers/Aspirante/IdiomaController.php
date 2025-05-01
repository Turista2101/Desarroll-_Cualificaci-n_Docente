<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstAgregarIdioma\NivelIdioma;
use App\Http\Requests\RequestAspirante\RequestIdioma\ActualizarIdiomaRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Idioma;
use App\Services\ArchivoService;
use App\Http\Requests\RequestAspirante\RequestIdioma\CrearIdiomaRequest; // Importar la clase de solicitud personalizada
use Illuminate\Support\Facades\DB; // Importar la clase DB para transacciones



class IdiomaController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Crear un nuevo idioma
    public function crearIdioma(CrearIdiomaRequest $request)
    {
        try {
            $idioma = DB::transaction(function () use ($request) {
                $datos = $request->validated();
                $datos['user_id'] = $request->user()->id;

                $idioma = Idioma::create($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $idioma, 'Idiomas');
                }

                return $idioma;
            });

            return response()->json([
                'mensaje' => 'Idioma y documento guardados correctamente',
                'idioma'  => $idioma,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el idioma.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Obtener todos los idiomas
    public function obtenerIdiomas(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $idiomas = Idioma::where('user_id', $user->id)
                ->with(['documentosIdioma:id_documento,documentable_id,archivo,estado'])
                ->orderBy('created_at')
                ->get();

            if ($idiomas->isEmpty()) {
                throw new \Exception('No se encontraron idiomas', 404);
            }

            $idiomas->each(function ($idioma) {
                $idioma->documentosIdioma->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });

            return response()->json(['idiomas' => $idiomas], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los idiomas.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Obtener un idioma por ID
    public function obtenerIdiomaPorId(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $idioma = Idioma::where('id_idioma', $id)
                ->where('user_id', $user->id)
                ->with(['documentosIdioma:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();

            $idioma->documentosIdioma->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });

            return response()->json(['idioma' => $idioma], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el idioma.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Actualizar un idioma
    public function actualizarIdioma(ActualizarIdiomaRequest $request, $id)
    {
        try {
            $idioma = DB::transaction(function () use ($request, $id) {
                $user = $request->user();

                $idioma = Idioma::where('id_idioma', $id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $datos = $request->validated();
                $idioma->update($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $idioma, 'Idiomas');
                }

                return $idioma;
            });

            return response()->json([
                'mensaje' => 'Idioma actualizado correctamente',
                'data'    => $idioma->fresh(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el idioma.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Eliminar un idioma
    public function eliminarIdioma(Request $request, $id)
    {
        try {
            $user = $request->user();

            $idioma = Idioma::where('id_idioma', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            DB::transaction(function () use ($idioma) {
                $this->archivoService->eliminarArchivoDocumento($idioma);
                $idioma->delete();
            });

            return response()->json(['mensaje' => 'Idioma eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el idioma.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
