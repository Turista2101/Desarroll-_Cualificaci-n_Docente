<?php
// Define el espacio de nombres del controlador
namespace App\Http\Controllers\Aspirante;
// Importaciones necesarias para que el controlador funcione correctamente
use Illuminate\Http\Request;
use App\Models\Aspirante\ProduccionAcademica;// Modelo de la producción académica
use App\Http\Requests\RequestAspirante\RequestProduccionAcademica\ActualizarProduccionAcademicaRequest; // Request de validación para actualizar
use App\Services\ArchivoService; // Servicio para manejar archivos
use App\Http\Requests\RequestAspirante\RequestProduccionAcademica\CrearProduccionAcademicaRequest; // Request de validación para crear
use Illuminate\Support\Facades\DB; // Para manejar transacciones con la base de datos

// Definición del controlador
class ProduccionAcademicaController
{
// Atributo para el servicio de archivos
   protected $archivoService;
   // Constructor que inyecta el servicio de archivos

   public function __construct(ArchivoService $archivoService)
   {
      $this->archivoService = $archivoService;
   }

   // Método para crear una nueva producción académica
   public function crearProduccion(CrearProduccionAcademicaRequest $request)
   {
      try {
         // Inicia una transacción para asegurar integridad de datos
         $produccionAcademica = DB::transaction(function () use ($request) {
            // Valida los datos recibidos
            $datos = $request->validated();
            // Asocia la producción académica al usuario autenticado
            $datos['user_id'] = $request->user()->id;
            // Crea el registro en la base de datos

            $produccionAcademica = ProduccionAcademica::create($datos);
            // Si se sube un archivo, lo guarda usando el servicio de archivos
            if ($request->hasFile('archivo')) {
               $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $produccionAcademica, 'ProduccionAcademica');
            }
         // Devuelve la producción académica creada
            return $produccionAcademica;
         });
         // Respuesta JSON de éxito
         return response()->json([
            'message' => 'Producción académica y documento guardados correctamente',
            'produccion_academica' => $produccionAcademica,
         ], 201);
      } catch (\Exception $e) {
      // Manejo de errores
         return response()->json([
            'message' => 'Error al crear la producción académica.',
            'error' => $e->getMessage()
         ], 500);
      }
   }

   // Método para obtener todas las producciones académicas del usuario autenticado
   public function obtenerProducciones(Request $request)
   {
      try {
         $user = $request->user();// Obtiene el usuario actual

         if (!$user) {
            throw new \Exception('Usuario no autenticado', 401);// Verifica autenticación
         }
         // Consulta las producciones académicas del usuario con sus documentos
         $producciones = ProduccionAcademica::where('user_id', $user->id)
            ->with(['documentosProduccionAcademica:id_documento,documentable_id,archivo,estado'])
            ->orderBy('created_at')
            ->get();
            
         // Verifica si hay resultados
         if ($producciones->isEmpty()) {
            return response()->json([
               'mensaje'=>'No se encontraron producciones',
               'producciones'=> null
            ], 200);
         }
         // Para cada producción, genera la URL pública del archivo
         $producciones->each(function ($produccion) {
            $produccion->documentosProduccionAcademica->each(function ($documento) {
               if (!empty($documento->archivo)) {
                  $documento->archivo_url = asset('storage/' . $documento->archivo);
               }
            });
         });
         // Devuelve las producciones en una respuesta JSON
         return response()->json(['producciones' => $producciones], 200);
      } catch (\Exception $e) {
         // Manejo de errores
         return response()->json([
            'message' => 'Error al obtener las producciones académicas.',
            'error' => $e->getMessage()
         ], $e->getCode() ?: 500);
      }
   }

   // Método para obtener una producción académica específica por ID
   public function obtenerProduccionPorId(Request $request, $id)
   {
      try {
         $user = $request->user();// Usuario autenticado

         if (!$user) {
            throw new \Exception('Usuario no autenticado', 401);
         }
         // Busca la producción académica del usuario por ID
         $produccion = ProduccionAcademica::where('id_produccion_academica', $id)
            ->where('user_id', $user->id)
            ->with(['documentosProduccionAcademica:id_documento,documentable_id,archivo,estado'])
            ->firstOrFail();
         // Agrega URL pública del archivo a cada documento
         $produccion->documentosProduccionAcademica->each(function ($documento) {
            if (!empty($documento->archivo)) {
               $documento->archivo_url = asset('storage/' . $documento->archivo);
            }
         });
         // Devuelve la producción en JSON
         return response()->json(['produccion' => $produccion], 200);
      } catch (\Exception $e) {
         // Error al buscar
         return response()->json([
            'message' => 'Error al obtener la producción académica.',
            'error' => $e->getMessage()
         ], $e->getCode() ?: 500);
      }
   }

   // Método para actualizar una producción académica
   public function actualizarProduccion(ActualizarProduccionAcademicaRequest $request, $id)
   {
      try {
         // Transacción para garantizar integridad
         $produccionAcademica = DB::transaction(function () use ($request, $id) {
            $user = $request->user();// Usuario actual
            // Busca la producción a actualizar

            $produccionAcademica = ProduccionAcademica::where('id_produccion_academica', $id)
               ->where('user_id', $user->id)
               ->firstOrFail();
         // Valida los datos recibidos
            $datos = $request->validated();
            // Actualiza la producción
            $produccionAcademica->update($datos);
            // Si hay un nuevo archivo, lo actualiza
            if ($request->hasFile('archivo')) {
               $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $produccionAcademica, 'ProduccionAcademica');
            }
            // Devuelve la producción actualizada
            return $produccionAcademica;
         });
         // Respuesta exitosa con datos actualizados
         return response()->json([
            'message' => 'Producción académica actualizada correctamente',
            'data' => $produccionAcademica->fresh(),
         ], 200);
      } catch (\Exception $e) {
         // Error al actualizar
         return response()->json([
            'message' => 'Error al actualizar la producción académica.',
            'error' => $e->getMessage()
         ], 500);
      }
   }

   // Método para eliminar una producción académica
   public function eliminarProduccion(Request $request, $id)
   {
      try {
         $user = $request->user();// Usuario autenticado

         // Busca la producción del usuario a eliminar

         $produccionAcademica = ProduccionAcademica::where('id_produccion_academica', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
         // Transacción para eliminar el archivo y luego el registro
         DB::transaction(function () use ($produccionAcademica) {
            $this->archivoService->eliminarArchivoDocumento($produccionAcademica);// Elimina archivo
            $produccionAcademica->delete(); // Elimina el registro de la base de datos
         });
         // Respuesta exitosa
         return response()->json(['message' => 'Producción académica eliminada correctamente'], 200);
      } catch (\Exception $e) {
         // Error al eliminar
         return response()->json([
            'message' => 'Error al eliminar la producción académica.',
            'error' => $e->getMessage()
         ], 500);
      }
   }
}
