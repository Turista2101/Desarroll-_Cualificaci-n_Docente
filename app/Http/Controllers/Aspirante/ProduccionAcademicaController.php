<?php

namespace App\Http\Controllers\Aspirante;

use Illuminate\Http\Request;
use App\Models\Aspirante\ProduccionAcademica;
use App\Http\Requests\RequestAspirante\RequestProduccionAcademica\ActualizarProduccionAcademicaRequest;
use App\Services\ArchivoService;
use App\Http\Requests\RequestAspirante\RequestProduccionAcademica\CrearProduccionAcademicaRequest;
use Illuminate\Support\Facades\DB;

class ProduccionAcademicaController
{
   protected $archivoService;

   public function __construct(ArchivoService $archivoService)
   {
      $this->archivoService = $archivoService;
   }

   // Crear una producción académica
   public function crearProduccion(CrearProduccionAcademicaRequest $request)
   {
      try {
         $produccionAcademica = DB::transaction(function () use ($request) {
            $datos = $request->validated();
            $datos['user_id'] = $request->user()->id;

            $produccionAcademica = ProduccionAcademica::create($datos);

            if ($request->hasFile('archivo')) {
               $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $produccionAcademica, 'ProduccionAcademica');
            }

            return $produccionAcademica;
         });

         return response()->json([
            'message' => 'Producción académica y documento guardados correctamente',
            'produccion_academica' => $produccionAcademica,
         ], 201);
      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error al crear la producción académica.',
            'error' => $e->getMessage()
         ], 500);
      }
   }

   // Obtener todas las producciones académicas
   public function obtenerProducciones(Request $request)
   {
      try {
         $user = $request->user();

         if (!$user) {
            throw new \Exception('Usuario no autenticado', 401);
         }

         $producciones = ProduccionAcademica::where('user_id', $user->id)
            ->with(['documentosProduccionAcademica:id_documento,documentable_id,archivo,estado'])
            ->orderBy('created_at')
            ->get();

         if ($producciones->isEmpty()) {
            throw new \Exception('No se encontraron producciones', 404);
         }

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
            'error' => $e->getMessage()
         ], $e->getCode() ?: 500);
      }
   }

   // Obtener una producción académica por ID
   public function obtenerProduccionPorId(Request $request, $id)
   {
      try {
         $user = $request->user();

         if (!$user) {
            throw new \Exception('Usuario no autenticado', 401);
         }

         $produccion = ProduccionAcademica::where('id_produccion_academica', $id)
            ->where('user_id', $user->id)
            ->with(['documentosProduccionAcademica:id_documento,documentable_id,archivo,estado'])
            ->firstOrFail();

         $produccion->documentosProduccionAcademica->each(function ($documento) {
            if (!empty($documento->archivo)) {
               $documento->archivo_url = asset('storage/' . $documento->archivo);
            }
         });

         return response()->json(['produccion' => $produccion], 200);
      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error al obtener la producción académica.',
            'error' => $e->getMessage()
         ], $e->getCode() ?: 500);
      }
   }

   // Actualizar una producción académica
   public function actualizarProduccion(ActualizarProduccionAcademicaRequest $request, $id)
   {
      try {
         $produccionAcademica = DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $produccionAcademica = ProduccionAcademica::where('id_produccion_academica', $id)
               ->where('user_id', $user->id)
               ->firstOrFail();

            $datos = $request->validated();
            $produccionAcademica->update($datos);

            if ($request->hasFile('archivo')) {
               $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $produccionAcademica, 'ProduccionAcademica');
            }

            return $produccionAcademica;
         });

         return response()->json([
            'message' => 'Producción académica actualizada correctamente',
            'data' => $produccionAcademica->fresh(),
         ], 200);
      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error al actualizar la producción académica.',
            'error' => $e->getMessage()
         ], 500);
      }
   }

   // Eliminar una producción académica
   public function eliminarProduccion(Request $request, $id)
   {
      try {
         $user = $request->user();

         $produccionAcademica = ProduccionAcademica::where('id_produccion_academica', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

         DB::transaction(function () use ($produccionAcademica) {
            $this->archivoService->eliminarArchivoDocumento($produccionAcademica);
            $produccionAcademica->delete();
         });

         return response()->json(['message' => 'Producción académica eliminada correctamente'], 200);
      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Error al eliminar la producción académica.',
            'error' => $e->getMessage()
         ], 500);
      }
   }
}
