<?php
// Define el espacio de nombres de este controlador
namespace App\Http\Controllers\Aspirante;
// Importa clases necesarias para manejar solicitudes, validaciones, modelos y servicios
use App\Http\Requests\RequestAspirante\RequestRut\ActualizarRutRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Rut;
use App\Services\ArchivoService;
use App\Http\Requests\RequestAspirante\RequestRut\CrearRutRequest;
use Illuminate\Support\Facades\DB;

// Define la clase controladora del RUT para el módulo del aspirante
class RutController
{
    // Servicio para gestionar archivos (guardar, actualizar, eliminar)
    protected $archivoService;
    // Constructor que inyecta la dependencia del servicio de archivos
    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }
    // Método para crear un nuevo RUT
    public function crearRut(CrearRutRequest $request)
    {
        try {
            // Obtiene el ID del usuario autenticado
            $usuarioId = $request->user()->id;
            // Verifica si ya existe un RUT para el usuario     
            $rutExistente = Rut::where('user_id', $usuarioId)->first();
            // Si ya hay un RUT, se devuelve un mensaje de conflicto (409)
            if ($rutExistente) {
                return response()->json([
                    'message' => 'Ya tienes un RUT registrado. No puedes crear otro.',
                ], 409);
            }
            // Se crea el RUT dentro de una transacción de base de datos  
            $rut = DB::transaction(function () use ($request) {
            // Obtiene los datos validados desde la solicitud
                $datos = $request->validated();
            // Asigna el ID del usuario autenticado
                $datos['user_id'] = $request->user()->id;
            // Crea el nuevo registro de RUT en la base de datos
                $rut = Rut::create($datos);
            // Si el usuario adjunta un archivo, se guarda
                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $rut, 'Rut');
                }
            // Retorna el modelo creado
                return $rut;
            });
            // Respuesta exitosa con el objeto creado y código 201 (creado)
            return response()->json([
                'message' => 'RUT creado exitosamente',
                'data' => $rut,
            ], 201);
        } catch (\Exception $e) {
            // Captura errores y devuelve respuesta con código 500
            return response()->json([
                'message' => 'Error al crear el RUT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Método para obtener el RUT del usuario autenticado
    public function obtenerRut(Request $request)
    {
        try {
            // Busca el RUT asociado al usuario actual y carga sus documentos relacionados
            $rut = Rut::where('user_id', $request->user()->id)
                ->with(['documentosRut:id_documento,documentable_id,archivo,estado'])
                ->first();// Si no se encuentra, lanza una excepción
            if (!$rut) {
                return response()->json([
                    'message' => 'No tienes RUT registrada aún.',
                    'rut' => null
                ], 200); // No es error, simplemente no tiene Rut aún
            }
                
        // Por cada documento asociado, se genera la URL pública del archivo
            foreach ($rut->documentosRut as $documento) {
                $documento->archivo_url = asset('storage/' . $documento->archivo);
            }
            // Devuelve el RUT encontrado junto con sus archivos
            return response()->json(['rut' => $rut], 200);
        } catch (\Exception $e) {
            // Captura errores y responde con código 500
            return response()->json([
                'message' => 'Error al obtener el RUT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Método para actualizar un RUT existente
    public function actualizarRut(ActualizarRutRequest $request)
    {
        try {
            // Ejecuta la actualización dentro de una transacción
            $rut = DB::transaction(function () use ($request) {
            // Busca el RUT del usuario autenticado
                $rut = Rut::where('user_id', $request->user()->id)->firstOrFail();
            // Actualiza el registro con los datos validados de la solicitud
                $rut->update($request->validated());
            // Si el usuario proporciona un nuevo archivo, se actualiza
                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $rut, 'Rut');
                }
            // Retorna el modelo actualizado
                return $rut;
            });
            // Devuelve respuesta con el RUT actualizado (se utiliza fresh() para obtener los datos actuales desde la DB)
            return response()->json([
                'message' => 'RUT actualizado exitosamente',
                'data' => $rut->fresh(),
            ], 200);
        } catch (\Exception $e) {
            // Manejo de errores con respuesta 500
            return response()->json([
                'message' => 'Error al actualizar el RUT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function eliminarRut(Request $request)
    // {
    //     try {
    //         $rut = Rut::where('user_id', $request->user()->id)->firstOrFail();

    //         $this->archivoService->eliminarArchivoDocumento($rut);
    //         $rut->delete();

    //         return response()->json(['message' => 'RUT eliminado correctamente'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Error al eliminar el RUT',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
