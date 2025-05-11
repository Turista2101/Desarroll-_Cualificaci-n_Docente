<?php
// Define el espacio de nombres del controlador dentro del módulo Aspirante

namespace App\Http\Controllers\Aspirante;
// Importa una constante de niveles de idioma
use App\Constants\ConstAgregarIdioma\NivelIdioma;
// Importa la solicitud personalizada para actualizar idioma
use App\Http\Requests\RequestAspirante\RequestIdioma\ActualizarIdiomaRequest;
// Importa la clase Request de Laravel
use Illuminate\Http\Request;
// Importa el modelo Idioma
use App\Models\Aspirante\Idioma;
// Importa el servicio de archivos
use App\Services\ArchivoService;
// Importa la solicitud personalizada para crear un idioma
use App\Http\Requests\RequestAspirante\RequestIdioma\CrearIdiomaRequest; // Importar la clase de solicitud personalizada
use Illuminate\Support\Facades\DB; // Importar la clase DB para transacciones



class IdiomaController
{
    // Declaración de una propiedad protegida para el servicio de archivos
    protected $archivoService;
    // Constructor que inyecta el servicio de archivos
    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }
    // Método para crear un nuevo idioma
    public function crearIdioma(CrearIdiomaRequest $request)
    {
        try {
            // Se ejecuta dentro de una transacción para asegurar consistencia
            $idioma = DB::transaction(function () use ($request) {
                $datos = $request->validated();
            // Se añade el ID del usuario autenticado
                $datos['user_id'] = $request->user()->id;
                // Crea el registro del idioma en la base de datos
                $idioma = Idioma::create($datos);
                // Si se envió un archivo, lo guarda usando el servicio
                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $idioma, 'Idiomas');
                }
                // Devuelve el idioma creado
                return $idioma;
            });
            // Retorna respuesta exitosa con el idioma creado
            return response()->json([
                'mensaje' => 'Idioma y documento guardados correctamente',
                'idioma'  => $idioma,
            ], 201);
        } catch (\Exception $e) {
            // Captura y retorna cualquier error que ocurra
            return response()->json([
                'message' => 'Error al crear el idioma.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Método para obtener todos los idiomas del usuario autenticado
    public function obtenerIdiomas(Request $request)
    {
        try {
        // Obtiene el usuario autenticado
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            // Consulta los idiomas asociados al usuario
            $idiomas = Idioma::where('user_id', $user->id)
                ->with(['documentosIdioma:id_documento,documentable_id,archivo,estado'])
                ->orderBy('created_at')
                ->get();

            if ($idiomas->isEmpty()) {
                return response()->json([
                    'mensaje'=> 'No se encontraron idiomas para el usuario.',
                    'idiomas'=> null
                ], 200);
            }
            // Agrega la URL completa al archivo en cada documento
            $idiomas->each(function ($idioma) {
                $idioma->documentosIdioma->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });
            // Retorna los idiomas en formato JSON
            return response()->json(['idiomas' => $idiomas], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'message' => 'Error al obtener los idiomas.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Método para obtener un idioma específico por su ID
    public function obtenerIdiomaPorId(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
            // Busca el idioma por ID e ID del usuario
            $idioma = Idioma::where('id_idioma', $id)
                ->where('user_id', $user->id)
                ->with(['documentosIdioma:id_documento,documentable_id,archivo,estado'])
                ->firstOrFail();// Falla si no encuentra el idioma
            // Añade URL completa a cada archivo
            $idioma->documentosIdioma->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });
            // Retorna el idioma encontrado
            return response()->json(['idioma' => $idioma], 200);
        } catch (\Exception $e) {
        // Manejo de errores
            return response()->json([
                'message' => 'Error al obtener el idioma.',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

      // Método para actualizar un idioma
    public function actualizarIdioma(ActualizarIdiomaRequest $request, $id)
    {
        try {
            // Ejecuta dentro de una transacción
            $idioma = DB::transaction(function () use ($request, $id) {
                $user = $request->user();
            // Busca el idioma por ID e ID del usuario
                $idioma = Idioma::where('id_idioma', $id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();
            // Valida los datos y actualiza el idioma
                $datos = $request->validated();
                $idioma->update($datos);
            // Si hay nuevo archivo, lo actualiza
                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $idioma, 'Idiomas');
                }

                return $idioma;
            });
        // Retorna idioma actualizado
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
        // Busca el idioma por ID e ID del usuario
            $idioma = Idioma::where('id_idioma', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            // Ejecuta eliminación dentro de una transacción
            DB::transaction(function () use ($idioma) {
                $this->archivoService->eliminarArchivoDocumento($idioma);// Elimina archivo
                $idioma->delete(); // Elimina el registro
            });
            // Retorna respuesta exitosa
            return response()->json(['mensaje' => 'Idioma eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el idioma.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
