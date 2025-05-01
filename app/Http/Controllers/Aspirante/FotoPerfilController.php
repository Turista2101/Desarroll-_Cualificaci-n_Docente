<?php

namespace App\Http\Controllers\Aspirante;

use App\Models\Aspirante\FotoPerfil;
use Illuminate\Http\Request;
use App\Services\ArchivoService;
use Illuminate\Support\Facades\DB;


class FotoPerfilController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Método para crear una nueva foto de perfil
    public function crearFotoPerfil(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $fotoPerfil = DB::transaction(function () use ($request) {
                $userId = $request->user()->id;

                // Verificar si ya existe una foto de perfil para el usuario
                $existeFoto = FotoPerfil::where('user_id', $userId)->exists();
                if ($existeFoto) {
                    throw new \Exception('Ya existe una foto de perfil para este usuario.', 409);
                }

                $fotoPerfil = FotoPerfil::create([
                    'user_id' => $userId,
                ]);

                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $fotoPerfil, 'FotoPerfil');
                }

                return $fotoPerfil;
            });

            return response()->json($fotoPerfil, 201);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => $e->getMessage() ?? 'Error al crear la foto de perfil.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Método para eliminar la foto de perfil
    public function eliminarFotoPerfil(Request $request)
    {
        try {
            $fotoPerfil = FotoPerfil::where('user_id', $request->user()->id)->first();

            if (!$fotoPerfil) {
                return response()->json(['mensaje' => 'No se encontró una foto de perfil para este usuario.'], 404);
            }

            // Usar el servicio para eliminar el documento y el archivo
            $this->archivoService->eliminarArchivoDocumento($fotoPerfil);

            $fotoPerfil->delete();

            return response()->json(['mensaje' => 'Foto de perfil eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar la foto de perfil.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Método para obtener la foto de perfil del usuario autenticado
    public function obtenerFotoPerfil(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            $fotoPerfil = FotoPerfil::where('user_id', $user->id)
                ->with(['documentosFotoPerfil:id_documento,documentable_id,archivo'])
                ->first();

            if (!$fotoPerfil) {
                throw new \Exception('No se encontró foto de perfil para este usuario', 404);
            }

            foreach ($fotoPerfil->documentosFotoPerfil as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }

            return response()->json(['fotoPerfil' => $fotoPerfil], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la foto de perfil',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
