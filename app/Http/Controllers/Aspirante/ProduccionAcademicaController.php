<?php

namespace App\Http\Controllers\Aspirante;

use Illuminate\Http\Request;
use App\Models\Aspirante\ProduccionAcademica;
use App\Http\Requests\RequestAspirante\RequestProduccionAcademica\ActualizarProduccionAcademicaRequest;
use App\Models\Aspirante\Documento; // Asegúrate de importar el modelo Documento
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RequestAspirante\RequestProduccionAcademica\CrearProduccionAcademicaRequest;
use Illuminate\Support\Facades\DB;
class ProduccionAcademicaController
{
   public function crearProduccion(CrearProduccionAcademicaRequest $request)
   {
      try {
         $produccionAcademica = DB::transaction(function () use ($request){

         // Validar los datos de entrada
         $datosProduccionAcademica = $request->validated();

         // Crear un nuevo registro de producción académica
         $produccionAcademica = ProduccionAcademica::create($datosProduccionAcademica);

         // Verificar si se guardó correctamente y si se envió un archivo
         if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('documentos/ProduccionAcademica', $nombreArchivo, 'public');

            // Guardar el documento relacionado con la producción académica
            Documento::create([
               'user_id'          => $request->user()->id,
               'archivo'          => str_replace('public/', '', $rutaArchivo),
               'estado'           => 'pendiente',
               'documentable_id'  => $produccionAcademica->id_produccion_academica,
               'documentable_type' => ProduccionAcademica::class,
            ]);
         }
         return $produccionAcademica;
      });
         return response()->json([
            'message'              => 'Producción académica y documento guardados correctamente',
            'produccion_academica' => $produccionAcademica,
         ], 201);

      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error al crear la producción académica o subir el archivo.',
            'error'   => $e->getMessage()
         ], 500);
      }
   }

   public function obtenerProducciones(Request $request)
   {
      try {
         $user = $request->user(); // Obtener el usuario autenticado

         // Verificar si el usuario está autenticado
         if (!$user) {
            throw new \Exception('Usuario no autenticado', 401);
         }

         // Obtener solo las producciones académicas que tienen documentos pertenecientes al usuario autenticado
         $producciones = ProduccionAcademica::whereHas('documentosProduccionAcademica', function ($query) use ($user) {
            $query->where('user_id', $user->id);
         })->with(['documentosProduccionAcademica' => function ($query) {
            $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
         }])
         ->orderBy('created_at') 
         ->get();


         if ($producciones->isEmpty()) {
            throw new \Exception('No se encontraron producciones', 404);
        }


         // Agregar la URL del archivo a cada documento si existe
         $producciones->each(function ($produccion) {
            $produccion->documentosProduccionAcademica->each(function ($documento) {
               if (!empty($documento->archivo)) {
                  $documento->archivo_url = asset('storage/' . $documento->archivo);
               }
            });
         });

         return response()->json(['producciones' => $producciones], 200);

      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error al obtener las producciones académicas.',
            'error'   => $e->getMessage()
         ],$e->getCode() ?: 500);
      }
   }

   public function obtenerProduccionPorId(Request $request, $id)
   {
      try {
         $user = $request->user(); // Obtener el usuario autenticado

         // Verificar si el usuario está autenticado
         if (!$user) {
            throw new \Exception('Usuario no autenticado', 401);
         }

         // Obtener solo las producciones académicas que tienen documentos pertenecientes al usuario autenticado
         $produccion = ProduccionAcademica::where('id_produccion_academica', $id)
            ->whereHas('documentosProduccionAcademica', function ($query) use ($user) {
               $query->where('user_id', $user->id);

            })
            ->with(['documentosProduccionAcademica' => function ($query) {
               $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
            }])
            ->orderBy('created_at') 
            ->first();


         if ($produccion->isEmpty()) {
            throw new \Exception('No se encontraron producciones', 404);
        }


         // Agregar la URL del archivo a cada documento si existe
            $produccion->documentosProduccionAcademica->each(function ($documento) {
               if (!empty($documento->archivo)) {
                  $documento->archivo_url = asset('storage/' . $documento->archivo);
               }
            });

         return response()->json(['producciones' => $produccion], 200);

      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error al obtener las producciones académicas.',
            'error'   => $e->getMessage()
         ],$e->getCode() ?: 500);
      }
   }


   // actualizar una producción académica
   public function actualizarProduccion(ActualizarProduccionAcademicaRequest $request, $id)
   {
      try {
         $produccionAcademica = DB::transaction(function () use ($request, $id) {
            
            $user = $request->user();

            // Buscar la producción académica que tenga documentos del usuario autenticado
            $produccionAcademica = ProduccionAcademica::whereHas('documentosProduccionAcademica', function ($query) use ($user) {
               $query->where('user_id', $user->id);
            })->where('id_produccion_academica', $id)->firstOrFail();

            // Validar solo los campos que se envían en la solicitud
            $datosProduccionAcademicaActualizar = $request->validated();
            $produccionAcademica->update($datosProduccionAcademicaActualizar);


            // Manejo del archivo
            if ($request->hasFile('archivo')) {
               $archivo = $request->file('archivo');
               $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
               $rutaArchivo = $archivo->storeAs('documentos/ProduccionAcademica', $nombreArchivo, 'public');

               // Buscar el documento asociado
               $documento = Documento::where('documentable_id', $produccionAcademica->id_produccion_academica)
                     ->where('documentable_type', ProduccionAcademica::class)
                     ->where('user_id', $user->id)
                     ->first();

               if ($documento) {
                     // Eliminar el archivo anterior
                     Storage::disk('public')->delete($documento->archivo);
                     

                     // Actualizar el documento
                     $documento->update([
                        'archivo' => str_replace('public/', '', $rutaArchivo),
                        'estado'  => 'pendiente',
                     ]);
               } else {
                     // Crear un nuevo documento si no existe
                     Documento::create([
                        'user_id'          => $user->id,
                        'archivo'          => str_replace('public/', '', $rutaArchivo),
                        'estado'           => 'pendiente',
                        'documentable_id'  => $produccionAcademica->id_produccion_academica,
                        'documentable_type' => ProduccionAcademica::class,
                     ]);
               }
           }

         return $produccionAcademica;
      });
         return response()->json([
            'message'              => 'Producción académica actualizada correctamente',
            'produccion_academica' => $produccionAcademica->refresh(),
         ], 200);

      }catch (\Exception $e) {
         return response()->json([
            'message' => 'Error inesperado al actualizar la producción académica.',
            'error'   => $e->getMessage()
         ], 500);
      }
   }

   // Eliminar una producción académica
   public function eliminarProduccion(Request $request, $id)
   {
      try {
         $user = $request->user(); // Usuario autenticado

         // Buscar la producción académica que tenga documentos del usuario autenticado
         $produccionAcademica = ProduccionAcademica::whereHas('documentosProduccionAcademica', function ($query) use ($user) {
            $query->where('user_id', $user->id);
         })->where('id_produccion_academica', $id)->first();

         if (!$produccionAcademica) {
            return response()->json(['error' => 'Producción académica no encontrada o no tienes permiso para eliminarla'], 403);
         }

         DB::transaction(function()use ($produccionAcademica) {
            // Eliminar los documentos relacionados
            foreach ($produccionAcademica->documentosProduccionAcademica as $documento) {
               // Eliminar el archivo del almacenamiento si existe
               if (!empty($documento->archivo) && Storage::exists('public/' . $documento->archivo)) {
                  Storage::delete('public/' . $documento->archivo);
               }
               $documento->delete(); // Eliminar el documento de la base de datos
            }
            // Eliminar la producción académica
            $produccionAcademica->delete();
         });

         return response()->json(['message' => 'Producción académica eliminada correctamente'], 200);

      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error inesperado al eliminar la producción académica.',
            'error'   => $e->getMessage()
         ], 500);
      }
   }
   
}