<?php
// Define el espacio de nombres del controlador
namespace App\Http\Controllers\Aspirante;
// Importaciones necesarias para el funcionamiento del controlador
use Illuminate\Http\Request;// Clase para manejar las solicitudes HTTP
use App\Models\Aspirante\InformacionContacto;// Modelo de la tabla 'informacion_contacto'
use App\Http\Requests\RequestAspirante\RequestInformacionContacto\ActualizarInformacionContactoRequest; // Request personalizado para validación al actualizar
use App\Http\Requests\RequestAspirante\RequestInformacionContacto\CrearInformacionContactoRequest; // Request personalizado para validación al crear
use App\Services\ArchivoService; // Servicio encargado de gestionar archivos
use Illuminate\Support\Facades\DB; // Facade para operaciones con base de datos y transacciones

// Definición del controlador
class InformacionContactoController
{
     // Propiedad protegida para manejar archivos
     protected $archivoService;

     // Constructor con inyección de dependencias del servicio de archivos
     public function __construct(ArchivoService $archivoService)
     {
         $this->archivoService = $archivoService;
     }
 
     // Método para crear un nuevo registro de información de contacto
     public function crearInformacionContacto(CrearInformacionContactoRequest $request)
     {
        try {
            // Se obtiene el ID del usuario autenticado
            $usuarioId = $request->user()->id;
            
            // Se verifica si ya existe información de contacto para ese usuario
            $informacionContactoExistente = InformacionContacto::where('user_id', $usuarioId)->first();
            // Si ya existe, se devuelve un error 409 (conflicto)
            if ($informacionContactoExistente) {
                return response()->json([
                    'message' => 'Ya tienes un registro de información de contacto. No puedes crear otro.',
                ], 409);
            }
            // Se inicia una transacción para crear el registro y guardar el archivo
            $informacionContacto = DB::transaction(function () use ($request) {
                $datos = $request->validated();
                // Se asocia el registro al usuario autenticado
                $datos['user_id'] = $request->user()->id;
                // Se crea el nuevo registro
                $informacionContacto = InformacionContacto::create($datos);
                // Si se envió un archivo, se guarda mediante el servicio
                if ($request->hasFile('archivo')) {
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $informacionContacto, 'LibretaMilitar');
                }
                // Se retorna el objeto creado
                return $informacionContacto;
            });
            // Respuesta exitosa
            return response()->json([
                'message' => 'Información de contacto y documento guardados correctamente',
                'data'    => $informacionContacto
            ], 201);
        } catch (\Exception $e) {
            // En caso de error, se devuelve un mensaje y el detalle del error
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
        // Se obtiene el usuario autenticado
            $user = $request->user();
        // Si no hay usuario autenticado, se lanza una excepción
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
        // Se obtiene la información de contacto del usuario junto con los documentos relacionados
            $informacionContacto = InformacionContacto::where('user_id', $user->id)
                ->with(['documentosInformacionContacto:id_documento,documentable_id,archivo,estado'])
                ->first();
            if (!$informacionContacto) {
                return response()->json([
                    'message' => 'No tienes EPS registrada aún.',
                    'informacionContacto' => null
                ], 200); // No es error, simplemente no tiene InformacionContacto aún
            }
                
            // Se genera la URL completa de cada archivo si existe
            foreach ($informacionContacto->documentosInformacionContacto as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }
            // Se retorna la información encontrada
            return response()->json(['informacion_contacto' => $informacionContacto], 200);
        } catch (\Exception $e) {
        // En caso de error, se devuelve el mensaje y el código correspondiente
            return response()->json([
                'message' => 'Error al obtener la información de contacto',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    // Método para actualizar la información de contacto existente
    public function actualizarInformacionContacto(ActualizarInformacionContactoRequest $request)
    {
        try {
        // Se utiliza una transacción para actualizar el registro y el archivo
            $informacionContacto = DB::transaction(function () use ($request) {
        // Se obtiene el usuario autenticado
                $user = $request->user();
        // Se busca el registro actual de información de contacto del usuario
                $informacionContacto = InformacionContacto::where('user_id', $user->id)->firstOrFail();
                // Se validan los nuevos datos
                $datos = $request->validated();
                // Se actualiza el registro con los nuevos datos
                $informacionContacto->update($datos);
                // Si hay un nuevo archivo, se actualiza mediante el servicio
                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $informacionContacto, 'LibretaMilitar');
                }
                // Se retorna el objeto actualizado
                return $informacionContacto;
            });
        // Respuesta exitosa con los datos frescos (refrescados de la base de datos)
            return response()->json([
                'message' => 'Información de contacto actualizada correctamente',
                'data'    => $informacionContacto->fresh()
            ], 200);
        } catch (\Exception $e) {
        // En caso de error, se devuelve el mensaje y el detalle del error
            return response()->json([
                'message' => 'Error al actualizar la información de contacto o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
