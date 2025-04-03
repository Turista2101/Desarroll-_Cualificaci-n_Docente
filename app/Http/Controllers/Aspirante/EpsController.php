<?php
namespace App\Http\Controllers\Aspirante;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Eps;
use App\Constants\ConstEps\TipoAfiliacion;
use App\Constants\ConstEps\EstadoAfiliacion;
use App\Constants\ConstEps\TipoAfiliado;
use App\Models\Aspirante\Documento;

class EpsController
{
    //Crear un registro de eps
    public function crearEps(Request $request)
    {
        //Validar los datos de entrada

        $validator = Validator::make(request()->all(), [

            'nombre_eps'                    => 'required|string|min:7|max:100',
            'tipo_afiliacion'               => 'required|in:' . implode(',', TipoAfiliacion::all()),//llamo a la constante tipo afiliacion para obtener los tipos de afiliacion
            'estado_afiliacion'             => 'required|in:' . implode(',', EstadoAfiliacion::all()),//llamo a la constante estado afiliacion para obtener los estados de afiliacion
            'fecha_afiliacion_efectiva'     => 'required|date',
            'fecha_finalizacion_afiliacion' => 'nullable|date',
            'tipo_afiliado'                 => 'required|in:' . implode(',', TipoAfiliado::all()),//llamo a la constante tipo afiliado para obtener los tipos de afiliado
            'numero_afiliado'               => 'nullable|string|max:100',
            'archivo'                       => 'required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
        ]);

        //Si la validación falla, se devuelve un mensaje de error

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // crear un registro de eps
        $eps = Eps::create([
            'nombre_eps'                    => $request->input('nombre_eps'),
            'tipo_afiliacion'               => $request->input('tipo_afiliacion'),
            'estado_afiliacion'             => $request->input('estado_afiliacion'),
            'fecha_afiliacion_efectiva'     => $request->input('fecha_afiliacion_efectiva'),
            'fecha_finalizacion_afiliacion' => $request->input('fecha_finalizacion_afiliacion'),
            'tipo_afiliado'                 => $request->input('tipo_afiliado'),
            'numero_afiliado'               => $request->input('numero_afiliado'),
        ]);

        // Verificar si se envió un archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('public/documentos/Eps', $nombreArchivo);
            
            // Guardar el documento relacionado con el eps
            Documento::create([
                'user_id'          => $request->user()->id,
                'archivo'          => str_replace('public/', 'storage/', 'Eps/', $rutaArchivo),
                'estado'           => 'pendiente',
                'documentable_id' => $eps->id_eps,
                'documentable_type' => Eps::class,
            ]);
        }

        // Devolver respuesta con la información de eps creada
        return response()->json([
            'message' => 'Eps y documento creado exitosamente',
            'data'    => $eps
        ], 201);
    }


    

    
    //Obtener la información de eps del usuario autenticado
    


    //Actualizar la información de eps del usuario autenticado
   








}
