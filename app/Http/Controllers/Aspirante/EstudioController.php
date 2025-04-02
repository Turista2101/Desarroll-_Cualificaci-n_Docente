<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstAgregarEstudio\Graduado;
use App\Constants\ConstAgregarEstudio\TiposEstudio;
use App\Constants\ConstAgregarEstudio\TituloConvalidado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Estudio;
use App\Models\Aspirante\Documento;

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
            'fecha_inicio'              => 'nullable|date',// volver este campo a requerido
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
            $rutaArchivo = $archivo->storeAs('public/documentos/Estudios', $nombreArchivo);

            // Guardar el documento relacionado con el estudio
            Documento::create([
                'user_id'        => $request->user()->id, // Usuario autenticado
                'archivo'        => str_replace('public/', 'storage/','Estudios/', $rutaArchivo),
                'estado'         => 'pendiente',
                'documentable_id' => $estudio->id_estudio, // Relación polimórfica
                'documentable_type' => Estudio::class,
            ]);
        }

        // Devolver respuesta con el registro creado
        return response()->json([
            'message' => 'Registro creado correctamente',
            'data'    => $estudio,
        ], 201);
    }






    
    //obtener todos los registros de estudio
    public function obtenerEstudio(Request $request)
    {

        // Obtener todos los estudios del usuario autenticado
        $estudios = Estudio::where('user_id', $request->user()->id)->get();
    
        // Si el usuario no tiene estudios, devolver un mensaje de error
        if ($estudios->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene estudios registrados'
            ], 404);
        }
    
        // Devolver los estudios encontrados con una respuesta estructurada
        return response()->json([
            'success' => true,
            'data' => $estudios
        ], 200);
    }


    //actualizar un registro de estudio
    public function actualizarEstudio(Request $request, $id)
    {
        
        //Buscar el registro de estudio por su id
        $estudio = Estudio::where('id', $id)->where('user_id', $request->user()->id)->first();
        
        //Si no se encuentra el registro, devolver un mensaje de error
        if (!$estudio) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        //Validar los datos de entrada
        $validator = Validator::make(request()->all(), [
            'tipo_estudio'              => 'sometimes|in:'. implode(',',TiposEstudio::all()),
            'graduado'                  => 'sometimes|in:'. implode(',',Graduado::all()),
            'institucion'               => 'sometimes|string|min:7|max:100',
            'fecha_graduacion'          => 'sometimes|nullable|date',
            'titulo_convalidado'        => 'sometimes|nullable|in:'. implode(',',TituloConvalidado::all()),
            'fecha_convalidacion'       => 'sometimes|nullable|date',
            'resolucion_convalidacion'  => 'sometimes|nullable|string|min:7|max:100',
            'posible_fecha_graduacion'  => 'sometimes|nullable|date',
            'titulo_estudio'            => 'sometimes|nullable|string|min:7|max:100',
            'fecha_inicio'              => 'sometimes|date',
            'fecha_fin'                 => 'sometimes|nullable|date',
        ]);

        //Si la validación falla, se devuelve un mensaje de error
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        //Actualizar el registro de estudio
        $estudio->update($request->only([
            'tipo_estudio',
            'graduado',
            'institucion',
            'fecha_graduacion',
            'titulo_convalidado',
            'fecha_convalidacion',
            'resolucion_convalidacion',
            'posible_fecha_graduacion',
            'titulo_estudio',
            'fecha_inicio',
            'fecha_fin'
        ]));

        //Devolver respuesta con el registro actualizado
        return response()->json([
            'message' => 'Registro actualizado correctamente',
            'data'    => $estudio->fresh()
        ], 200);
    }


    

    
    //eliminar un registro de estudio
    public function eliminarEstudio(Request $request, $id)
    {

        //Buscar el registro de estudio por su id
        $estudio = Estudio::where('id', $id)->where('user_id', $request->user()->id)->first();
        
        //Si no se encuentra el registro, devolver un mensaje de error
        if (!$estudio) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        //Eliminar el registro de estudio
        $estudio->delete();

        //Devolver respuesta con un mensaje de éxito
        return response()->json(['message' => 'Registro eliminado correctamente'], 200);
    }


}