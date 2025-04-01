<?php
namespace App\Http\Controllers\Aspirante;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Eps;
use App\Constants\ConstEps\TipoAfiliacion;
use App\Constants\ConstEps\EstadoAfiliacion;
use App\Constants\ConstEps\TipoAfiliado;

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
            'fecha_afiliacion_efectiva'      => 'required|date',
            'fecha_finalizacion_afiliacion' => 'nullable|date',
            'tipo_afiliado'                 => 'required|in:' . implode(',', TipoAfiliado::all()),//llamo a la constante tipo afiliado para obtener los tipos de afiliado
            'numero_afiliado'               => 'nullable|string|max:100',
        ]);

        //Si la validación falla, se devuelve un mensaje de error

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // crear un registro de eps
        Eps::create([
            'nombre_eps'                    => $request->input('nombre_eps'),
            'tipo_afiliacion'               => $request->input('tipo_afiliacion'),
            'estado_afiliacion'             => $request->input('estado_afiliacion'),
            'fecha_afiliacion_efectiva'     => $request->input('fecha_afiliacion_efectiva'),
            'fecha_finalizacion_afiliacion' => $request->input('fecha_finalizacion_afiliacion'),
            'tipo_afiliado'                 => $request->input('tipo_afiliado'),
            'numero_afiliado'               => $request->input('numero_afiliado'),
        ]);

        //Devolver respuesta con el registro creado
        return response()->json(['menssage'=>'Usuario creado exitosamente'], 201);
    }


    

    
    //Obtener la información de eps del usuario autenticado
    public function obtenerEps(Request $request)

    {
        //obtener la información de eps del usuario autenticado
        $eps = Eps::where('user_id', $request->user()->id)->first();

        //sino se encuentra la información de eps, se devuelve un mensaje de error
        if (!$eps) {
            return response()->json(['message' => 'No se ha encontrado información de eps para el usuario autenticado'], 404);
        }
        //devolver respuesta con la información de eps
        return response()->json([
            'message' => 'Información de EPS obtenida exitosamente',
            'data'    => $eps
        ], 200);
        
    }




    //Actualizar la información de eps del usuario autenticado
    public function actualizarEps(Request $request)
    {
   
        //buscar la información de eps del usuario autenticado
        $eps = Eps::where('user_id', $request->user()->id)->first();

        //si no se encuentra la información de eps, se devuelve un mensaje de error
        if (!$eps) {
            return response()->json(['message' => 'No se ha encontrado información de eps para el usuario autenticado'], 404);
        }

        //validar los datos de entrada
        $validator = Validator::make(request()->all(), [

            'nombre_eps'                    => 'sometimes|string|min:7|max:100',
            'tipo_afiliacion'               => 'sometimes|in:' . implode(',', TipoAfiliacion::all()),//llamo a la constante tipo afiliacion para obtener los tipos de afiliacion
            'estado_afiliacion'             => 'sometimes|in:' . implode(',', EstadoAfiliacion::all()),//llamo a la constante estado afiliacion para obtener los estados de afiliacion
            'fecha_afiliacion_efectiva'     => 'sometimes|date',
            'fecha_finalizacion_afiliacion' => 'sometimes|nullable|date',
            'tipo_afiliado'                 => 'sometimes|in:' . implode(',', TipoAfiliado::all()),//llamo a la constante tipo afiliado para obtener los tipos de afiliado
            'numero_afiliado'               => 'sometimes|nullable|string|max:100',
        ]);

        //si la validación falla, se devuelve un mensaje de error
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 400);
        }
        
        //actualizar la información de eps
        $eps->update($request->only([
            'nombre_eps',
            'tipo_afiliacion',
            'estado_afiliacion',
            'fecha_afiliacion_efectiva',
            'fecha_finalizacion_afiliacion',
            'tipo_afiliado',
            'numero_afiliado'
        ]));

        //devolver respuesta con la información de eps actualizada
        return response()->json([
            'message' => 'Información de EPS actualizada exitosamente',
            'data'    => $eps->fresh()
        ], 200);
    }









}
