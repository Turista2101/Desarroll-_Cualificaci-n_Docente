<?php
// Define el espacio de nombres para el controlador

namespace App\Http\Controllers\Aspirante;
// Importa el modelo FotoPerfil

use App\Models\Aspirante\FotoPerfil;
// Importa la clase Request para manejar peticiones HTTP

use Illuminate\Http\Request;
// Importa el servicio personalizado para manejar archivos

use App\Services\ArchivoService;
// Importa la clase DB para realizar transacciones

use Illuminate\Support\Facades\DB;

// Define la clase controladora para manejar la lógica de la foto de perfil

class FotoPerfilController
{ 
       // Propiedad protegida para almacenar el servicio de archivos

    protected $archivoService;
    // Constructor que inyecta el servicio de archivos

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    // Método para crear una nueva foto de perfil
    public function crearFotoPerfil(Request $request)
    {
        try {
            // Valida que se haya enviado un archivo tipo imagen

            $request->validate([
                'archivo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            
            // Ejecuta  dentro de una transaccion para garantizar consistencia

            $fotoPerfil = DB::transaction(function () use ($request) {
                $userId = $request->user()->id;

                // Verificar si ya existe una foto de perfil para el usuario
                $existeFoto = FotoPerfil::where('user_id', $userId)->exists();
                if ($existeFoto) {
                // Lanza una excepción si ya hay una foto

                    throw new \Exception('Ya existe una foto de perfil para este usuario.', 409);
                }
                // Crea el registro de foto de perfil

                $fotoPerfil = FotoPerfil::create([
                    'user_id' => $userId,
                ]);
                // Si se adjuntó un archivo, lo guarda usando el servicio


                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $fotoPerfil, 'FotoPerfil');
                }
                // Retorna el registro creado

                return $fotoPerfil;
            });
            // Retorna la respuesta con el registro creado y código 201
            return response()->json($fotoPerfil, 201);
        } catch (\Exception $e) {
        // Si ocurre un error, devuelve un mensaje y el error correspondiente

            return response()->json([
                'mensaje' => $e->getMessage() ?? 'Error al crear la foto de perfil.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Método para eliminar la foto de perfil del usuario
    public function eliminarFotoPerfil(Request $request)
    {
        try {
        // Busca la foto de perfil del usuario autenticado

            $fotoPerfil = FotoPerfil::where('user_id', $request->user()->id)->first();
            // Si no existe una foto, retorna error 404

            if (!$fotoPerfil) {
                return response()->json([
                    'mensaje' => 'No se encontró una foto de perfil para este usuario.',
                    'fotoPerfil' => null
                ], 200);
            }

            // Usar el servicio para eliminar el documento y el archivo
            $this->archivoService->eliminarArchivoDocumento($fotoPerfil);
            // Elimina el registro de la base de datos

            $fotoPerfil->delete();
            // Retorna respuesta de éxito

            return response()->json(['mensaje' => 'Foto de perfil eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            // En caso de error, retorna mensaje con código

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
            // Obtiene el usuario autenticado

            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            // Busca la foto de perfil y carga los documentos asociados
            $fotoPerfil = FotoPerfil::where('user_id', $user->id)
                ->with(['documentosFotoPerfil:id_documento,documentable_id,archivo'])
                ->first();
            // Si no se encuentra ninguna foto, lanza excepción

            if (!$fotoPerfil) {
                return response()->json([
                    'mensaje' => 'No tienes foto de perfil registrada aún.',
                    'fotoPerfil' => null
                ], 200); // No es error, simplemente no tiene foto de perfil aún
            }
            // Agrega la URL completa del archivo para cada documento asociado

            foreach ($fotoPerfil->documentosFotoPerfil as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }
            // Devuelve la foto de perfil con los documentos adjuntos

            return response()->json(['fotoPerfil' => $fotoPerfil], 200);
        } catch (\Exception $e) {
            // Devuelve mensaje de error en caso de falla

            return response()->json([
                'message' => 'Error al obtener la foto de perfil',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
