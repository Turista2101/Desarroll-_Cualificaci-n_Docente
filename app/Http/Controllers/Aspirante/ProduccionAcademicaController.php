<?php

namespace App\Http\Controllers\Aspirante;

use App\Http\Controllers\Controller; // Importar la clase base Controller
use Illuminate\Http\Request;
use App\Models\Aspirante\ProduccionAcademica;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Documento; // Asegúrate de importar el modelo Documento
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Storage;
class ProduccionAcademicaController
{
   //guardar un nuevo registro 
   public function crearProduccion(Request $request)
   {
      // Validar los datos de entrada
      $validator = Validator::make($request->all(), [

         'ambito_divulgacion_id' => 'required|integer|exists:ambito_divulgacions,id_ambito_divulgacion',
         'titulo' => 'required|string|max:255',
         'numero_autores' => 'required|integer',
         'medio_divulgacion' => 'required|string|max:255',
         'fecha_divulgacion' => 'nullable|date',// volver este campo a requerido
         'archivo' => 'required|file|mimes:pdf,doc,docx|max:2048',
      ]);
      if ($validator->fails()) {
         return response()->json(['errors' => $validator->errors()], 422);
      }

      // Crear un nuevo registro de producción académica
      $produccionAcademica = ProduccionAcademica::create([
         'ambito_divulgacion_id' => $request->input('ambito_divulgacion_id'),
         'titulo' => $request->input('titulo'),
         'numero_autores' => $request->input('numero_autores'),
         'medio_divulgacion' => $request->input('medio_divulgacion'),
         'fecha_divulgacion' => $request->input('fecha_divulgacion'),
         
      ]);
      //verificar si se guardo correctamente
      if($request->hasFile('archivo')){
         $archivo = $request->file('archivo');
         $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
         $rutaArchivo = $archivo->storeAs('documentos/ProduccionAcademica', $nombreArchivo,'public');

         //Guardar el documento relacionado con la producción académica
         Documento::create([
            'user_id' => $request->user()->id,
            'archivo' => str_replace('public/','', $rutaArchivo),
            'estado'=>'pendiente',
            'documentable_id' => $produccionAcademica->id_produccion_academica,
            'documentable_type' => ProduccionAcademica::class,

         ]);
      }
      return response()->json([
         'message' => 'Producción académica  y documento guardados correctamente',
         'produccion_academica' => $produccionAcademica,
      ], 201);
   
   }

   public function obtenerProducciones(Request $request)
   {
      $user = $request->user(); // Obtener el usuario autenticado

      // Verificar si el usuario está autenticado
      if (!$user) {
         return response()->json(['error' => 'Usuario no autenticado'], 401);
      }

      // Obtener solo las producciones académicas que tienen documentos pertenecientes al usuario autenticado
      $producciones = ProduccionAcademica::whereHas('documentosProduccionAcademica', function ($query) use ($user) {
         $query->where('user_id', $user->id);
      })->with(['documentosProduccionAcademica' => function ($query) {
         $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
      }])->get();

      // Agregar la URL del archivo a cada documento si existe
      $producciones->each(function ($produccion) {
         $produccion->documentos->each(function ($documento) {
               if (!empty($documento->archivo)) {
                  $documento->archivo_url = asset('storage/' . $documento->archivo);
               }
         });
      });

      return response()->json(['producciones' => $producciones], 200);
   }





   public function actualizarProduccion(Request $request, $id)
   {
      $user = $request->user();

      // Buscar la producción académica que tenga documentos del usuario autenticado
      $produccionAcademica = ProduccionAcademica::whereHas('documentosProduccionAcademica', function ($query) use ($user) {
         $query->where('user_id', $user->id);
      })->where('id_produccion_academica', $id)->firstOrFail();

      // Validar solo los campos que se envían en la solicitud
      $validator = Validator::make($request->all(), [
         'ambito_divulgacion_id' => 'sometimes|required|integer|exists:ambito_divulgacions,id_ambito_divulgacion',
         'titulo'               => 'sometimes|required|string|max:255',
         'numero_autores'       => 'sometimes|required|integer',
         'medio_divulgacion'    => 'sometimes|required|string|max:255',
         'fecha_divulgacion'    => 'sometimes|nullable|date',
         'archivo'              => 'sometimes|file|mimes:pdf,doc,docx|max:2048',
      ]);

      if ($validator->fails()) {
         return response()->json($validator->errors()->toJson(), 400);
      }

      // Actualizar los datos directamente
      $data = $request->only([
         'ambito_divulgacion_id', 'titulo', 'numero_autores', 'medio_divulgacion', 'fecha_divulgacion'
      ]);

      $produccionAcademica->update($data);

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
               Storage::disk('public')->delete($documento->archivo);
               $documento->update([
                  'archivo' => str_replace('public/', '', $rutaArchivo),
                  'estado'  => 'pendiente',
               ]);
         } else {
               Documento::create([
                  'user_id'          => $user->id,
                  'archivo'          => str_replace('public/', '', $rutaArchivo),
                  'estado'           => 'pendiente',
                  'documentable_id'  => $produccionAcademica->id_produccion_academica,
                  'documentable_type' => ProduccionAcademica::class,
               ]);
         }
      }

      return response()->json([
         'message'              => 'Producción académica actualizada correctamente',
         'produccion_academica' => $produccionAcademica->refresh(),
      ], 200);
   }

   // Eliminar una producción académica
   public function eliminarProduccion(Request $request, $id)
   {
      $user = $request->user(); // Usuario autenticado

      // Buscar la producción académica que tenga documentos del usuario autenticado
      $produccionAcademica = ProduccionAcademica::whereHas('documentosProduccionAcademica', function ($query) use ($user) {
         $query->where('user_id', $user->id);
      })->where('id_produccion_academica', $id)->first();

      if (!$produccionAcademica) {
         return response()->json(['error' => 'Producción académica no encontrada o no tienes permiso para eliminarla'], 403);
      }

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

      return response()->json(['message' => 'Producción académica eliminada correctamente'], 200);
   }


      // Obtener un registro de producción académica por ID
      public function obtenerProduccionPorId(Request $request, $id)
   {
      $user = $request->user();

      // Buscar la producción académica que tenga documentos del usuario autenticado
      $produccionAcademica = ProduccionAcademica::whereHas('documentosProduccionAcademica', function ($query) use ($user) {
         $query->where('user_id', $user->id);
      })->with(['documentosProduccionAcademica' => function ($query) {
         $query->select('id_documento', 'documentable_id', 'archivo', 'user_id');
      }])->where('id_produccion_academica', $id)->first();

      if (!$produccionAcademica) {
         return response()->json(['error' => 'Producción académica no encontrada o no tienes permiso para verla'], 403);
      }

      // Agregar la URL del archivo si existe
      $produccionAcademica->documentosProduccionAcademica->each(function ($documento) {
         if (!empty($documento->archivo)) {
               $documento->archivo_url = asset('storage/' . $documento->archivo);
         }
      });

      return response()->json(['produccion_academica' => $produccionAcademica], 200);
   }
   
}