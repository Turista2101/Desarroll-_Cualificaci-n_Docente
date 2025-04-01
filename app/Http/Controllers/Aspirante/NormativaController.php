<?php

namespace App\Http\Controllers;

use App\Models\Normativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NormativaController
{
    // Obtener todas las normativas
    public function obtenerNormativas(Request $request)
    {
        $normativas = Normativa::all();

        if ($normativas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay normativas registradas'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $normativas
        ], 200);
    }

    // Obtener una normativa específica
    public function mostrarNormativaEspecifica($id)
    {
        $normativa = Normativa::find($id);

        if (!$normativa) {
            return response()->json([
                'success' => false,
                'message' => 'Normativa no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $normativa
        ], 200);
    }


    // Actualizar una normativa existente
    public function actualizarNormativa(Request $request, $id)
    {
        $normativa = Normativa::find($id);

        if (!$normativa) {
            return response()->json([
                'success' => false,
                'message' => 'Normativa no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre'        => 'required|string|max:255',
            'descripcion'   => 'nullable|string',
            'tipo'          => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $normativa->update($request->only([
            'nombre',
            'descripcion',
            'tipo'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Normativa actualizada correctamente',
            'data' => $normativa
        ], 200);
    }

    // Eliminar una normativa
    public function eliminarNormativa($id)
    {
        $normativa = Normativa::find($id);

        if (!$normativa) {
            return response()->json([
                'success' => false,
                'message' => 'Normativa no encontrada'
            ], 404);
        }

        $normativa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Normativa eliminada correctamente'
        ], 200);
    }
}