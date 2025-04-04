<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstAgregarIdioma\NivelIdioma;
use App\Http\Controllers\Controller; // Importar la clase base Controller
use Illuminate\Http\Request;
use App\Models\Aspirante\Idioma;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Documento; // Importar el modelo Documento
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Importar la clase Log para depuración

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
            $rutaArchivo = $archivo->storeAs('documentos/Idiomas', $nombreArchivo, 'public');


            // Guardar el documento relacionado con el idioma
            Documento::create([
                'user_id'        => $request->user()->id, // Usuario autenticado
                'archivo'        => str_replace('public/','', $rutaArchivo),
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
    public function obtenerIdiomas(Request $request)
    {
        $user = $request->user();

        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Obtener todos los idiomas relacionados con el usuario autenticado
        $idiomas = Idioma::whereHas('documentosIdioma', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['documentosIdioma' => function ($query) {
            $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
        }])->get();

        // Agregar el Url a cada documento si existe
        $idiomas->each(function ($idioma) {
            if ($idioma->documentosIdioma) {
                $idioma->documentosIdioma->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            }
        });
        return response()->json(['idiomas' => $idiomas], 200);

    }


    // Actualizar un registro de idioma
    public function actualizarIdioma(Request $request, $id)
    {

        $user = $request->user();

        // Buscar el estudio que tenga documentos del usuario autenticado
        $idioma = Idioma::whereHas('documentosIdioma', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id_idioma', $id)->firstOrFail();

        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'idioma'             => 'required|string|max:255',
            'institucion_idioma' => 'required|string|max:255',
            'fecha_certificado'  => 'nullable|date',//poner este campo otra ves a requerido
            'nivel'              => 'required|in:' . implode(',', NivelIdioma::all()),
            'archivo'            => 'nullable|file|mimes:pdf,jpg,png|max:2048', // Validación de archivo
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Depurar: Ver qué datos se están enviando
        Log::info('Datos recibidos para actualización:', $request->all());
        Log::info('Datos actuales del modelo:', $idioma->toArray());

        // Actualizar los datos directamente
         // Actualizar los datos directamente
         $data = $request->only([
            'idioma',
            'institucion_idioma',
            'fecha_certificado',
            'nivel',
        ]);

        $idioma->update($data);

        // Verificar si se envió un archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('documentos/Idiomas', $nombreArchivo, 'public');

            // Buscar el documento asociado
            $documento = Documento::where('documentable_id', $idioma->id_idioma)
                ->where('documentable_type', Idioma::class)
                ->where('user_id', $user->id)
                ->first();
            if ($documento) {
                Storage::disk('public')->delete($documento->archivo);
                $documento->update([
                    'archivo' => str_replace('public/','', $rutaArchivo),
                    'estado'  => 'pendiente',
                ]);
            } else {
                // Crear un nuevo documento si no existe
                Documento::create([
                    'user_id'        => $request->user()->id,
                    'archivo'        => str_replace('public/','', $rutaArchivo),
                    'estado'         => 'pendiente',
                    'documentable_id' => $idioma->id_idioma,
                    'documentable_type' => Idioma::class,
                ]);
            }
        }

        return response()->json([
            'mensaje'  => 'Idioma actualizado correctamente',
            'idioma'   => $idioma,
        ], 200);
    }


    // Eliminar un registro de idioma
    public function eliminarIdioma(Request $request, $id)
    {
        $user = $request->user();

        // Buscar el idioma que tenga documentos del usuario autenticado
        $idioma = Idioma::whereHas('documentosIdioma', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id_idioma', $id)->first();

        if (!$idioma) {
            return response()->json(['error' => 'Idioma no encontrado'], 404);
        }
        // Eliminar los documentos relacionados
        foreach ($idioma->documentosIdioma as $documento) {
            // Eliminar el archivo del almacenamiento si existe
            if (!empty($documento->archivo) && Storage::exists('public/' . $documento->archivo)) {
                Storage::delete('public/' . $documento->archivo);
            }
            $documento->delete(); // Eliminar el documento de la base de datos
        }
        // Eliminar el idioma
        $idioma->delete();
        
        return response()->json(['mensaje' => 'Idioma eliminado correctamente'], 200);
        
    }

    
}

