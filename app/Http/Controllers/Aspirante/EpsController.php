<?php
// Este controlador maneja la creación, obtención y actualización de registros EPS para aspirantes.
namespace App\Http\Controllers\Aspirante;
// Importación de clases necesarias
use App\Http\Requests\RequestAspirante\RequestEps\ActualizarEpsRequest;// Request para validar actualización de EPS
use Illuminate\Http\Request;// Clase para manejar solicitudes HTTP
use App\Models\Aspirante\Eps;// Modelo Eps
use App\Services\ArchivoService;// Servicio para manejar archivos
use Illuminate\Support\Facades\DB;// Facade para trabajar con transacciones de base de datos
use App\Http\Requests\RequestAspirante\RequestEps\CrearEpsRequest;// Request para validar creación de EPS


// Definición del controlador EpsController
class EpsController
{
   // Servicio de archivos que se usará para guardar o actualizar documentos
    protected $archivoService;
    // Constructor: inyecta el servicio de archivos
    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

      /**
     * Crear un nuevo registro de EPS y subir un archivo si se proporciona.
     */
    public function crearEps(CrearEpsRequest $request)
    {
        try {
            // Obtener el ID del usuario autenticado
            $usuarioId = $request->user()->id;

            // Verificar si el usuario ya tiene una EPS registrada
            $epsExistente = Eps::where('user_id', $usuarioId)->first();
              // Si ya existe una EPS para este usuario, retornar error 409 (conflicto)
            if ($epsExistente) {
                return response()->json([
                    'message' => 'Ya tienes una EPS registrada. No puedes crear otra.',
                ], 409);
            }
            // Ejecutar la creación dentro de una transacción para asegurar consistencia
            $eps = DB::transaction(function () use ($request) {
                 // Obtener datos validados del request
                $datos = $request->validated();
                 // Asociar la EPS al usuario autenticado
                $datos['user_id'] = $request->user()->id;

                $eps = Eps::create($datos);
                 // Si hay archivo, guardarlo
                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $eps, 'Eps');
                }

                return $eps;
            });
            // Respuesta si llega hacer  exitoso
            return response()->json([
                'message' => 'EPS y documento creado exitosamente',
                'data'    => $eps
            ], 201);// Código 201: es igual a que esta creado
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la EPS o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
 /**
     * Obtener los datos de la EPS y su documento asociado del usuario autenticado.
     */
    public function obtenerEps(Request $request)
    {
        try {
            $user = $request->user();//obtener el usuario autenticado

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);// Este error da  si no está logueado 
            }

             // Buscar EPS con documentos asociados
            $eps = Eps::where('user_id', $user->id)
                ->with(['documentosEps:id_documento,documentable_id,archivo,estado'])
                ->first();//error si no existe

            if (!$eps) {
                return response()->json([
                    'message' => 'No tienes EPS registrada aún.',
                    'eps' => null
                ], 200); // No es error, simplemente no tiene EPS aún
            }
            
             // Generar URL accesible para cada archivo
            foreach ($eps->documentosEps as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }
            // Retornar información de EPS
            return response()->json(['eps' => $eps], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'message' => 'Error al obtener la información de EPS',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

        /**
     * Actualizar los datos de la EPS del usuario autenticado, y actualizar archivo si se proporciona.
     */
    public function actualizarEps(ActualizarEpsRequest $request)
    {
        try {
            // Iniciar transacción para actualizar EPS
            $eps = DB::transaction(function () use ($request) {
                $user = $request->user();// Obtener usuario autenticado
            // Buscar EPS actual
                $eps = Eps::where('user_id', $user->id)->firstOrFail();

                $datos = $request->validated();// Validar datos nuevos
                $eps->update($datos);// Actualizar EPS
                  // Si hay archivo nuevo, actualizarlo
                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $eps, 'Eps');
                }

                return $eps;
            });
            // Respuesta con datos actualizados
            return response()->json([
                'message' => 'EPS actualizado exitosamente',
                'data'    => $eps->fresh()
            ], 200);

        } catch (\Exception $e) {
            //manejo de errores
            return response()->json([
                'message' => 'Error al actualizar el EPS',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
