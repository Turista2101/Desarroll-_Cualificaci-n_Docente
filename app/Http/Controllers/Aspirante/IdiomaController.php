<?php

namespace App\Http\Controllers\Aspirante;

use App\Http\Controllers\Controller; // Importar la clase base Controller
use Illuminate\Http\Request;
use App\Models\Aspirante\Idioma;
use Illuminate\Support\Facades\Validator;

class IdiomaController
{
    // Guardar un nuevo idioma en la base de datos
    




    // Obtener todos los registros de idiomas del usuario autenticado
    public function obtenerIdiomas(Request $request)
    {
        $idiomas = Idioma::where('user_id', $request->user()->id)->get();

        if ($idiomas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene idiomas registrados'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'idiomas' => $idiomas
        ], 200);
    }


    // Actualizar un idioma existente
    public function actualizarIdioma(Request $request, $id)
    {
        
        // Buscar el registro de idioma por su ID y el ID del usuario
        $idioma = Idioma::where('id', $id)->where('user_id',$request->user()->id)->first();

        if (!$idioma) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'idioma'                 => 'sometimes|string|max:255',
            'institucion_idioma'     => 'sometimes|nullable|string|max:255',
            'fecha_certificado'      => 'sometimes|nullable|date', // Fecha válida
            'nivel'                  => 'sometimes|string|max:50', // Nivel del idioma
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Actualizar el registro de idioma
        $idioma->update($request->only([
            'idioma',
            'institucion_idioma',
            'fecha_certificado',
            'nivel'
        ]));

        // Devolver la respuesta
        return response()->json([
            'mensaje' => 'Idioma actualizado correctamente',
            'idioma'  => $idioma ->fresh() // Obtener el registro actualizado
        ]);
    }

    // Eliminar un idioma
    public function eliminarIdioma(Request $request, $id)
    {

        // Buscar el registro de idioma por su ID y el ID del usuario
        $idioma = Idioma::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$idioma) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        // Eliminar el registro de idioma
        $idioma->delete();

        // Devolver respuesta con un mensaje de éxito
        return response()->json(['message' => 'Registro eliminado correctamente'], 200);
    }
}