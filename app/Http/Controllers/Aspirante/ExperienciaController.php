<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstAgregarExperiencia\TiposExperiencia;
use App\Constants\ConstAgregarExperiencia\TrabajoActual;
use Illuminate\Http\Request;
use App\Models\Aspirante\Experiencia;
use App\Models\Aspirante\Documento; // Importar el modelo Documento
use Illuminate\Support\Facades\Validator;

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
            $rutaArchivo = $archivo->storeAs('public/documentos/Experiencias', $nombreArchivo);

            // Guardar el documento relacionado con la experiencia
            Documento::create([
                'user_id'        => $request->user()->id, // Usuario autenticado
                'archivo'        => str_replace('public/', 'storage/','Experiencias/', $rutaArchivo),
                'estado'         => 'pendiente',
                'documentable_id' => $experiencia->id_experiencia, // Relación polimórfica
                'documentable_type' => Experiencia::class,
            ]);
        }

        // Devolver respuesta con el registro creado
        return response()->json([
            'message' => ' Idioma y documento guardados correctamente',
            'Experiencia'    => $experiencia->fresh()
        ], 201);
    }


    // Obtener todos los registros de experiencia
   
    

    // Actualizar un registro de experiencia
    


    // Eliminar un registro de experiencia
    
}