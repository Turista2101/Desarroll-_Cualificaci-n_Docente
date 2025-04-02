<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstAgregarIdioma\NivelIdioma;
use App\Http\Controllers\Controller; // Importar la clase base Controller
use Illuminate\Http\Request;
use App\Models\Aspirante\Idioma;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Documento; // Importar el modelo Documento

class IdiomaController
{
    // Guardar un nuevo idioma en la base de datos
    public function crearIdioma(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'idioma'             => 'required|string|max:255',
            'institucion_idioma' => 'required|string|max:255',
            'fecha_certificado'  => 'nullable|date',//poner este campo otra ves a requerido
            'nivel'              => 'required|in:' . implode(',', NivelIdioma::all()),
            'archivo'            => 'required|file|mimes:pdf,jpg,png|max:2048', // Validación de archivo
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear un nuevo idioma
        $idioma = Idioma::create([
            'idioma'             => $request->input('idioma'),
            'institucion_idioma' => $request->input('institucion_idioma'),
            'fecha_certificado'  => $request->input('fecha_certificado'),
            'nivel'              => $request->input('nivel'),
        ]);

        // Verificar si se envió un archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('public/documentos/Idiomas', $nombreArchivo);


            // Guardar el documento relacionado con el idioma
            Documento::create([
                'user_id'        => $request->user()->id, // Usuario autenticado
                'archivo'        => str_replace('public/', 'storage/','Idiomas/', $rutaArchivo),
                'estado'         => 'pendiente',
                'documentable_id' => $idioma->id_idioma, // Relación polimórfica
                'documentable_type' => Idioma::class,
            ]);
        }

        return response()->json([
            'mensaje'  => 'Idioma y documento guardados correctamente',
            'idioma'   => $idioma,
        ], 201);
    }


    // Obtener todos los registros de idiomas del usuario autenticado
   
}
