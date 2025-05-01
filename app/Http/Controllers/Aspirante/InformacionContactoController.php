<?php

namespace App\Http\Controllers\Aspirante;

use Illuminate\Http\Request;
use App\Models\Aspirante\InformacionContacto;
use App\Http\Requests\RequestAspirante\RequestInformacionContacto\ActualizarInformacionContactoRequest;
use App\Http\Requests\RequestAspirante\RequestInformacionContacto\CrearInformacionContactoRequest;
use App\Services\ArchivoService;
use Illuminate\Support\Facades\DB;

class InformacionContactoController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Crear un registro de información de contacto
    public function crearInformacionContacto(CrearInformacionContactoRequest $request)
    {
        try {
            $usuarioId = $request->user()->id;
            
            $informacionContactoExistente = InformacionContacto::where('user_id', $usuarioId)->first();

            if ($informacionContactoExistente) {
                return response()->json([
                    'message' => 'Ya tienes un registro de información de contacto. No puedes crear otro.',
                ], 409);
            }
            
            $informacionContacto = DB::transaction(function () use ($request) {
                $datos = $request->validated();
                $datos['user_id'] = $request->user()->id;

                $informacionContacto = InformacionContacto::create($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $informacionContacto, 'LibretaMilitar');
                }

                return $informacionContacto;
            });

            return response()->json([
                'message' => 'Información de contacto y documento guardados correctamente',
                'data'    => $informacionContacto
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la información de contacto o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Obtener la información de contacto del usuario autenticado
    public function obtenerInformacionContacto(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $informacionContacto = InformacionContacto::where('user_id', $user->id)
                ->with(['documentosInformacionContacto:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();

            foreach ($informacionContacto->documentosInformacionContacto as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }

            return response()->json(['informacion_contacto' => $informacionContacto], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la información de contacto',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Actualizar información de contacto
    public function actualizarInformacionContacto(ActualizarInformacionContactoRequest $request)
    {
        try {
            $informacionContacto = DB::transaction(function () use ($request) {
                $user = $request->user();

                $informacionContacto = InformacionContacto::where('user_id', $user->id)->firstOrFail();

                $datos = $request->validated();
                $informacionContacto->update($datos);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $informacionContacto, 'LibretaMilitar');
                }

                return $informacionContacto;
            });

            return response()->json([
                'message' => 'Información de contacto actualizada correctamente',
                'data'    => $informacionContacto->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la información de contacto o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
