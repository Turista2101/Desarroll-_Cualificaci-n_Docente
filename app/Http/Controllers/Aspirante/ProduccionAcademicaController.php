<?php

namespace App\Http\Controllers\Aspirante;

use App\Http\Controllers\Controller; // Importar la clase base Controller
use Illuminate\Http\Request;
use App\Models\Aspirante\ProduccionAcademica;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Documento; // Asegúrate de importar el modelo Documento
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
         $rutaArchivo = $archivo->storeAs('public/documentos/ProduccionAcademica', $nombreArchivo);

         //Guardar el documento relacionado con la producción académica
         Documento::create([
            'user_id' => $request->user()->id,
            'archivo' => str_replace('public/','storage/','ProduccionAcademica/', $rutaArchivo),
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


   // Actualizar un registro de producción académica
   
}