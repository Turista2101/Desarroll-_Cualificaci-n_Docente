<?php

namespace App\Http\Controllers\Aspirante;

use App\Models\Aspirante\FotoPerfil;
use Illuminate\Http\Request;
use App\Models\Aspirante\Documento;

class FotoPerfilController
{
    
    // Método para crear una nueva foto de perfil
    public function crearFotoPerfil(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'archivo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            
            // Verificar si el usuario está autenticado
            $userId = $request->user()->id;

            // Verificar si ya existe una foto de perfil para el usuario
            $existeFoto = FotoPerfil::where('user_id', $userId)->exists();
            if ($existeFoto) {
                return response()->json([
                    'mensaje' => 'Ya existe una foto de perfil para este usuario.'
                ], 409); // Código 409: conflicto
            }

            // Crear entrada en FotoPerfil
            $fotoPerfil = FotoPerfil::create([
                'user_id' => $userId,
            ]);

            if ($request->hasFile('archivo')) {
                $archivo = $request->file('archivo');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $rutaArchivo = $archivo->storeAs('documentos/FotoPerfil', $nombreArchivo, 'public');

                // Guardar el documento relacionado
                Documento::create([
                    'archivo'           => str_replace('public/', '', $rutaArchivo),
                    'documentable_id'   => $fotoPerfil->id_foto_perfil,
                    'documentable_type' => FotoPerfil::class,
                ]);
            }

            return response()->json($fotoPerfil, 201);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear la foto de perfil.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Método para eliminar la foto de perfil
    public function eliminarFotoPerfil(Request $request)
    {
        try {
            // Buscar la foto de perfil del usuario autenticado
            $fotoPerfil = FotoPerfil::where('user_id', $request->user()->id)->first();

            if (!$fotoPerfil) {
                return response()->json(['mensaje' => 'No se encontró una foto de perfil para este usuario.'], 404);
            }

            // Buscar el documento asociado (relación polimórfica)
            $documento = Documento::where('documentable_id', $fotoPerfil->id_foto_perfil)
                                ->where('documentable_type', FotoPerfil::class)
                                ->first();

            if ($documento) {
                // Eliminar el archivo del almacenamiento
                $archivoPath = storage_path('app/public/' . $documento->archivo); // ruta completa
                if (file_exists($archivoPath)) {
                    unlink($archivoPath); // eliminar archivo del sistema de archivos
                }

                // Eliminar el documento de la base de datos
                $documento->delete();
            }

            // Eliminar la entrada de FotoPerfil
            $fotoPerfil->delete();

            return response()->json(['mensaje' => 'Foto de perfil eliminada correctamente.'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar la foto de perfil.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Método para obtener la foto de perfil del usuario autenticado
    public function obtenerFotoPerfil(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
    
            // Verificar si el usuario está autenticado
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
    
            // Obtener la foto de perfil relacionada con el usuario
            $fotoPerfil = FotoPerfil::where('user_id', $user->id)
                ->with(['documentosFotoPerfil' => function ($query) {
                    $query->select('id_documento', 'documentable_id', 'archivo');
                }])
                ->first();
    
            // Verificar si la foto de perfil existe
            if (!$fotoPerfil) {
                throw new \Exception('No se encontró foto de perfil para este usuario', 404);
            }
    
            // Agregar la URL del archivo de la foto de perfil
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
