<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstRut\CodigoCiiu;
use App\Constants\ConstRut\TipoPersona;
use  Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Rut;


class RutController
{
    //Crear un nuevo registro de rut
    public function cerarRut(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'user_id'                       => 'required|exists:users,id',
            'nombre_rut'                    => 'required|string|min:7|max:100',
            'razon_social'                  => 'required|string|min:7|max:100',
            'tipo_persona'                  => 'required|in:' . implode(',', TipoPersona::all()),
            'codigo_ciiu'                   => 'required|in:' . implode(',', CodigoCiiu::all()),
            'Responsabilidades_tributarias' => 'required|string|min:7|max:100',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $rut = Rut::create([
            'user_id'                       => $request->input('user_id'),
            'nombre_rut'                    => $request->input('nombre_rut'),
            'razon_social'                  => $request->input('razon_social'),
            'tipo_persona'                  => $request->input('tipo_persona'),
            'codigo_ciiu'                   => $request->input('codigo_ciiu'),
            'Responsabilidades_tributarias' => $request->input('Responsabilidades_tributarias'),

        ]);
        
        
        //Devolver respuesta con el registro creado
        return response()->json($rut, 201);

    }


    //Obtener la información de rut del usuario autenticado
    public function obtenerRut(Request $request)
    {
        //obtener el id del usuario autenticado
        $user_id = $request->user()->id;

        //obtener la información de rut del usuario autenticado
        $rut = Rut::where('user_id', $user_id)->first();

        //Si no se encuentra el registro, devolver un mensaje de error
        if (!$rut) {
            return response()->json(['error' => 'No se encontró el registro de RUT'], 404);
        }
        //Devolver la información de rut
        return response()->json($rut, 200);
    }


    
    //Actualizar la información de rut del usuario autenticado
    public function actualizarRut(Request $request)
    {
        //obtener el id del usuario autenticado
        $user_id = $request->user()->id;

        //obtener la información de rut del usuario autenticado
        $rut = Rut::where('user_id', $user_id)->first();

        //Si no se encuentra el registro, devolver un mensaje de error
        if (!$rut) {
            return response()->json(['error' => 'No se encontró el registro de RUT'], 404);
        }

        //validar los datos de entrada
        $validator = Validator::make(request()->all(), [
            'nombre_rut'                    => 'required|string|min:7|max:100',
            'razon_social'                  => 'required|string|min:7|max:100',
            'tipo_persona'                  => 'required|in:' . implode(',', TipoPersona::all()),
            'codigo_ciiu'                   => 'required|in:' . implode(',', CodigoCiiu::all()),
            'Responsabilidades_tributarias' => 'required|string|min:7|max:100',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        //Actualizar la información de rut
        $rut->update([
            'nombre_rut'                    => $request->input('nombre_rut'),
            'razon_social'                  => $request->input('razon_social'),
            'tipo_persona'                  => $request->input('tipo_persona'),
            'codigo_ciiu'                   => $request->input('codigo_ciiu'),
            'Responsabilidades_tributarias' => $request->input('Responsabilidades_tributarias'),

        ]);
        
        //Devolver la información de rut actualizada
        return response()->json($rut, 200);
    }





    

}
