<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstAgregarEstudio\Graduado;
use App\Constants\ConstAgregarEstudio\TiposEstudio;
use App\Constants\ConstAgregarEstudio\TituloConvalidado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Estudio;
use App\Models\Aspirante\Documento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EstudioController
{
    //crear un registro de estudio
    public function crearEstudio(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make(request()->all(), [
            'tipo_estudio'              => 'required|in:' . implode(',', TiposEstudio::all()),
            'graduado'                  => 'required|in:' . implode(',', Graduado::all()),
            'institucion'               => 'required|string|min:7|max:100',
            'fecha_graduacion'          => 'nullable|date',
            'titulo_convalidado'        => 'required|in:' . implode(',', TituloConvalidado::all()),
            'fecha_convalidacion'       => 'nullable|date',
            'resolucion_convalidacion'  => 'nullable|string|min:7|max:100',
            'posible_fecha_graduacion'  => 'nullable|date',
            'titulo_estudio'            => 'nullable|string|min:7|max:100',
            'fecha_inicio'              => 'nullable|date', // volver este campo a requerido
            'fecha_fin'                 => 'nullable|date',
            'archivo'                   => 'required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
        ]);

        // Si la validación falla, se devuelve un mensaje de error
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Crear el registro de estudio
        $estudio = Estudio::create([
            'tipo_estudio'             => $request->input('tipo_estudio'),
            'graduado'                 => $request->input('graduado'),
            'institucion'              => $request->input('institucion'),
            'fecha_graduacion'         => $request->input('fecha_graduacion'),
            'titulo_convalidado'       => $request->input('titulo_convalidado'),
            'fecha_convalidacion'      => $request->input('fecha_convalidacion'),
            'resolucion_convalidacion' => $request->input('resolucion_convalidacion'),
            'posible_fecha_graduacion' => $request->input('posible_fecha_graduacion'),
            'titulo_estudio'           => $request->input('titulo_estudio'),
            'fecha_inicio'             => $request->input('fecha_inicio'),
            'fecha_fin'                => $request->input('fecha_fin'),
        ]);

        // Verificar si se envió un archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('documentos/Estudios', $nombreArchivo, 'public');

            // Guardar el documento relacionado con el estudio
            Documento::create([
                'user_id'        => $request->user()->id, // Usuario autenticado
                'archivo'        => str_replace('public/', '', $rutaArchivo),
                'estado'         => 'pendiente',
                'documentable_id' => $estudio->id_estudio, // Relación polimórfica
                'documentable_type' => Estudio::class,
            ]);
        }

        // Devolver respuesta con el registro creado
        return response()->json([
            'message' => 'Estudio y documento creados exitosamente',
            'data'    => $estudio,
        ], 201);
    }



    // Obtener estudios del usuario autenticado
    public function obtenerEstudios(Request $request)
    {
        $user = $request->user(); // Obtiene el usuario autenticado

        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Obtener solo los estudios que tienen documentos pertenecientes al usuario autenticado
        $estudios = Estudio::whereHas('documentosEstudio', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['documentosEstudio' => function ($query) {
            $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
        }])->get();

        // Agregar la URL del archivo a cada documento si existe
        $estudios->each(function ($estudio) {
            $estudio->documentosEstudio->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });
        });

        return response()->json(['estudios' => $estudios], 200);
    }

    

    // Actualizar estudio
    public function actualizarEstudio(Request $request, $id)
    {
        $user = $request->user();

        // Buscar el estudio que tenga documentos del usuario autenticado
        $estudio = Estudio::whereHas('documentosEstudio', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id_estudio', $id)->firstOrFail(); // Asegurar que use la clave primaria id_estudio

        // Validar solo los campos que se envían en la solicitud
        $validator = Validator::make($request->all(), [
            'tipo_estudio'              => 'sometimes|required|in:' . implode(',', TiposEstudio::all()),
            'graduado'                  => 'sometimes|required|in:' . implode(',', Graduado::all()),
            'institucion'               => 'sometimes|required|string|min:7|max:100',
            'fecha_graduacion'          => 'sometimes|nullable|date',
            'titulo_convalidado'        => 'sometimes|required|in:' . implode(',', TituloConvalidado::all()),
            'fecha_convalidacion'       => 'sometimes|nullable|date',
            'resolucion_convalidacion'  => 'sometimes|nullable|string|min:7|max:100',
            'posible_fecha_graduacion'  => 'sometimes|nullable|date',
            'titulo_estudio'            => 'sometimes|nullable|string|min:7|max:100',
            'fecha_inicio'              => 'sometimes|required|date', 
            'fecha_fin'                 => 'sometimes|nullable|date',
            'archivo'                   => 'sometimes|file|mimes:pdf,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Depurar: Ver qué datos se están enviando
        Log::info('Datos recibidos para actualización:', $request->all());
        Log::info('Datos actuales del modelo:', $estudio->toArray());

        // Actualizar los datos directamente
        $data = $request->only([
            'tipo_estudio', 'graduado', 'institucion', 'fecha_graduacion',
            'titulo_convalidado', 'fecha_convalidacion', 'resolucion_convalidacion',
            'posible_fecha_graduacion', 'titulo_estudio', 'fecha_inicio', 'fecha_fin'
        ]);

        $estudio->update($data);

        // Manejo del archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('documentos/Estudios', $nombreArchivo, 'public');

            // Buscar el documento asociado
            $documento = Documento::where('documentable_id', $estudio->id_estudio)
                ->where('documentable_type', Estudio::class)
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
                    'user_id'        => $user->id,
                    'archivo'        => str_replace('public/', '', $rutaArchivo),
                    'estado'         => 'pendiente',
                    'documentable_id' => $estudio->id_estudio,
                    'documentable_type' => Estudio::class,
                ]);
            }
        }

        return response()->json([
            'message' => 'Estudio actualizado correctamente',
            'data'    => $estudio->refresh()
        ], 200);
    }

    

    // Eliminar estudio
    public function eliminarEstudio(Request $request, $id)
    {
        $user = $request->user(); // Usuario autenticado
    
        // Buscar el estudio que tenga documentos del usuario autenticado
        $estudio = Estudio::whereHas('documentosEstudio', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id_estudio', $id)->first();
    
        if (!$estudio) {
            return response()->json(['error' => 'Estudio no encontrado o no tienes permiso para eliminarlo'], 403);
        }
    
        // Eliminar los documentos relacionados
        foreach ($estudio->documentosEstudio as $documento) {
            // Eliminar el archivo del almacenamiento si existe
            if (!empty($documento->archivo) && Storage::exists('public/' . $documento->archivo)) {
                Storage::delete('public/' . $documento->archivo);
            }
            $documento->delete(); // Eliminar el documento de la base de datos
        }
    
        // Eliminar el estudio
        $estudio->delete();
    
        return response()->json(['message' => 'Estudio eliminado correctamente'], 200);
    }

}