<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aspirante\Experiencia;
use Illuminate\Support\Facades\Validator;

class ExperienciaController
{
    // Crear un registro de experiencia

    
    //obtener un registro de experiencia por id
    public function obtenerExperiencia(Request $request)
    {

        $experiencias = Experiencia::where('user_id', $request->user()->id)->get();

        // Verificar si se encontraron experiencias
        if ($experiencias->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene experiencias registradas'
            ], 404);
        }
        // Devolver la respuesta
        return response()->json([
            'success' => true,
            'experiencias' => $experiencias
        ], 200);
       
    }



    // Actualizar una experiencia existente
    public function actualizarExperiencia(Request $request, $id)
    {
    
        //Buscar el registro de estudio por su id
        $experiencia = Experiencia::where('id', $id)->where('user_id', $request->user()->id)->first();

        //Si no se encuentra el registro, devolver un mensaje de error
        if (!$experiencia) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        $validator = Validator::make(request()->all(), [
            'tipo_experiencia'             => 'sometimes|string|max:255',
            'institucion_experiencia'      => 'sometimes|string|max:255',
            'cargo'                        => 'sometimes|string|max:255',
            'trabajo_actual'               => 'sometimes|boolean', // Debe ser true o false
            'intensidad_horaria'           => 'sometimes|nullable|integer|min:1', // Horas mínimas
            'fecha_inicio'                 => 'sometimes|date', // Fecha válida
            'fecha_finalizacion'           => 'sometimes|nullable|date|after_or_equal:fecha_inicio', // Fecha válida y posterior a la de inicio
            'fecha_expedicion_certificado' => 'sometimes|nullable|date', // Fecha válida
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        //Actualizar el registro de experiencia
        $experiencia->update($request->only([
            'tipo_experiencia',
            'institucion_experiencia',
            'cargo',
            'trabajo_actual',
            'intensidad_horaria',
            'fecha_inicio',
            'fecha_finalizacion',
            'fecha_expedicion_certificado'
        ]));

        //Devolver la respuesta
        return response()->json([
            'mensaje' => 'Experiencia actualizada correctamente',
            'experiencia' => $experiencia
        ]);

        
    }

    

    // Eliminar una experiencia
    public function eliminarExperiencia(Request $request, $id)
    {
      
        //Buscar el registro de experiencia por su id
        $experiencia = Experiencia::where('id', $id)->where('user_id', $request->user()->id)->first();

        //Si no se encuentra el registro, devolver un mensaje de error
        if (!$experiencia) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        //Eliminar el registro de experiencia
        $experiencia->delete();

        //Devolver respuesta con un mensaje de éxito
        return response()->json(['message' => 'Registro eliminado correctamente'], 200);
    }
   
}