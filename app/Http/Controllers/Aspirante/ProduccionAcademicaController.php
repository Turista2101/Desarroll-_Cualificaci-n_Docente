<?php

namespace App\Http\Controllers\Aspirante;

use App\Http\Controllers\Controller; // Importar la clase base Controller
use Illuminate\Http\Request;
use App\Models\Aspirante\ProduccionAcademica;
use Illuminate\Support\Facades\Validator;

class ProduccionAcademicaController
{
    

    //obtener todas las producciones académicas
    public function obtenerProduccionesAcademicas(Request $request)
    {
        // Obtener todas las producciones académicas del usuario autenticado
        $producciones = ProduccionAcademica::where('user_id', $request->user()->id)->get();

        //si el usuario no tiene producciones académicas, devolver un mensaje
        if ($producciones->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene producciones académicas registradas'
            ], 404);
        }
        // Devolver respuesta con las producciones académicas
        return response()->json([
            'success' => true,
            'data' => $producciones
        ], 200);
    }



    // Actualizar una producción académica existente
    public function actualizarProduccionAcademica(Request $request, $id)
    {
        // Buscar la producción académica por su id y el id del usuario
        $produccion = ProduccionAcademica::where('id', $id)->where('user_id', $request->user()->id)->first();
        // Si no se encuentra la producción académica, devolver un mensaje de error
        if (!$produccion) {
            return response()->json(['error' => 'Producción académica no encontrada'], 404);
        }
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'titulo'            => 'sometimes|string|max:255',
            'numero_autores'    => 'sometimes|integer|min:1', // Mínimo 1 autor
            'medio_divulgacion' => 'sometimes|string|max:255',
            'fecha_divulgacion' => 'sometimes|date', // Fecha válida
        ]);
        // Si la validación falla, devolver un mensaje de error
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        // Actualizar los campos de la producción académica
        $produccion->update($request->only([
            'titulo',
            'numero_autores',
            'medio_divulgacion',
            'fecha_divulgacion'
        ]));

        // Devolver respuesta con la producción académica actualizada
        return response()->json([
            'menssage' => 'Producción académica actualizada correctamente',
            'data' => $produccion
        ], 200);

    }

    
    // Eliminar una producción académica
    public function eliminarProduccionAcademica(Request $request, $id)  
    {

        // Buscar la producción académica por su id y el id del usuario
        $produccion = ProduccionAcademica::where('id', $id)->where('user_id', $request->user()->id)->first();

        // Si no se encuentra la producción académica, devolver un mensaje de error
        if (!$produccion) {
            return response()->json(['error' => 'Producción académica no encontrada'], 404);
        }

        // Eliminar la producción académica
        $produccion->delete();

        // Devolver respuesta con un mensaje de éxito
        return response()->json(['message' => 'Producción académica eliminada correctamente'], 200);
    }
}
