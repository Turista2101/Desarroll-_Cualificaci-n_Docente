<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstAgregarExperiencia\TiposExperiencia;
use App\Constants\ConstAgregarExperiencia\TrabajoActual;
use Illuminate\Http\Request;
use App\Models\Aspirante\Experiencia;
use App\Models\Aspirante\Documento;// Importar el modelo Documento
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class ExperienciaController
{
    // Crear un registro de experiencia
    public function crearExperiencia(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make(request()->all(), [
            'tipo_experiencia'             => 'required|string|in:'. implode(',', TiposExperiencia::all()),
            'institucion_experiencia'      => 'required|string|min:3|max:100',
            'cargo'                        => 'required|string|min:3|max:100',
            'trabajo_actual'               => 'required|in:' . implode(',', TrabajoActual::all()),
            'intensidad_horaria'           => 'nullable|integer|min:1|max:168',
            'fecha_inicio'                 => 'nullable|date',// volver este campo a requerido
            'fecha_finalizacion'           => 'nullable|date|after_or_equal:fecha_inicio',
            'fecha_expedicion_certificado' => 'nullable|date',
            'archivo'                      => 'required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
        ]);

        // Si la validación falla, se devuelve un mensaje de error
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Crear el registro de experiencia
        $experiencia = Experiencia::create([
            'tipo_experiencia'             => $request->input('tipo_experiencia'),
            'institucion_experiencia'      => $request->input('institucion_experiencia'),
            'cargo'                        => $request->input('cargo'),
            'trabajo_actual'               => $request->input('trabajo_actual'),
            'intensidad_horaria'           => $request->input('intensidad_horaria'),
            'fecha_inicio'                 => $request->input('fecha_inicio'),
            'fecha_finalizacion'           => $request->input('fecha_finalizacion'),
            'fecha_expedicion_certificado' => $request->input('fecha_expedicion_certificado'),
        ]);

        // Verificar si se envió un archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('documentos/Experiencias', $nombreArchivo, 'public');

            // Guardar el documento relacionado con la experiencia
            Documento::create([
                'user_id'        => $request->user()->id, // Usuario autenticado
                'archivo'        => str_replace('public/', '', $rutaArchivo),
                'estado'         => 'pendiente',
                'documentable_id' => $experiencia->id_experiencia, // Relación polimórfica
                'documentable_type' => Experiencia::class,
            ]);
        }

        // Devolver respuesta con el registro creado
        return response()->json([
            'message' => ' Experiencia y documento guardados correctamente',
            'Experiencia'    => $experiencia
        ], 201);
    }
    
    
    // Obtener todos los registros de experiencia
    public function obtenerExperiencias(Request $request)
    {
        $user = $request->user(); // Obtiene el usuario autenticado
    
        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }
    
        // Obtener solo las experiencias que tienen documentos pertenecientes al usuario autenticado
        $experiencias = Experiencia::whereHas('documentosExperiencia', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['documentosExperiencia' => function ($query) {
            $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
        }])->get();
    
        // Agregar la URL del archivo a cada documento si existe
        $experiencias->each(function ($experiencia) {
            $experiencia->documentosExperiencia->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });
        });
    
        return response()->json(['experiencias' => $experiencias], 200);
    }
    

    
    // Actualizar un registro de experiencia
    public function actualizarExperiencia(Request $request, $id)
    {
        $user = $request->user();

        // Buscar la experiencia que tenga documentos del usuario autenticado
        $experiencia = Experiencia::whereHas('documentosExperiencia', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id_experiencia', $id)->firstOrFail(); // Asegurar que use la clave primaria id_experiencia

        // Validar solo los campos que se envían en la solicitud
        $validator = Validator::make($request->all(), [
            'tipo_experiencia'             => 'sometimes|required|string|in:' . implode(',', TiposExperiencia::all()),
            'institucion_experiencia'      => 'sometimes|required|string|min:3|max:100',
            'cargo'                        => 'sometimes|required|string|min:3|max:100',
            'trabajo_actual'               => 'sometimes|required|in:' . implode(',', TrabajoActual::all()),
            'intensidad_horaria'           => 'sometimes|nullable|integer|min:1|max:168',
            'fecha_inicio'                 => 'sometimes|required|date',
            'fecha_finalizacion'           => 'sometimes|nullable|date|after_or_equal:fecha_inicio',
            'fecha_expedicion_certificado' => 'sometimes|nullable|date',
            'archivo'                      => 'sometimes|file|mimes:pdf,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }


        // Actualizar los datos directamente
        $data = $request->only([
            'tipo_experiencia', 'institucion_experiencia', 'cargo', 'trabajo_actual',
            'intensidad_horaria', 'fecha_inicio', 'fecha_finalizacion', 'fecha_expedicion_certificado'
        ]);

        $experiencia->update($data);

        // Manejo del archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('documentos/Experiencias', $nombreArchivo, 'public');

            // Buscar el documento asociado
            $documento = Documento::where('documentable_id', $experiencia->id_experiencia)
                ->where('documentable_type', Experiencia::class)
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
                    'documentable_id' => $experiencia->id_experiencia,
                    'documentable_type' => Experiencia::class,
                ]);
            }
        }

        return response()->json([
            'message' => 'Experiencia actualizada correctamente',
            'data'    => $experiencia->refresh()
        ], 200);
    }

    


    // Eliminar un registro de experiencia
    public function eliminarExperiencia(Request $request, $id)
    {
        $user = $request->user();

        // Buscar la experiencia que tenga documentos del usuario autenticado
        $experiencia = Experiencia::whereHas('documentosExperiencia', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id_experiencia', $id)->firstOrFail(); // Asegurar que use la clave primaria id_experiencia

        // Eliminar el documento asociado
        foreach ($experiencia->documentosExperiencia as $documento) {
            // Eliminar el archivo del almacenamiento si existe
            if (!empty($documento->archivo) && Storage::exists('public/' . $documento->archivo)) {
                Storage::delete('public/' . $documento->archivo);
            }
            $documento->delete(); // Eliminar el documento de la base de datos
        }

        // Eliminar la experiencia
        $experiencia->delete();

        return response()->json(['message' => 'Experiencia eliminada correctamente'], 200);
    }
}