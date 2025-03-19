<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\InformacionContacto;


class InformacionContactoController
{
    public function crearInformacionContacto(Request $request){
        //Validar los datos de entrada

        $validator = Validator::make(request()->all(), [
            
            'genero' => 'required|in:MASCULINO,FEMENINO',
            'estado_civil' => 'required|in:SOLTERO,CASADO,UNION_LIBRE,DIVORCIADO,VIUDO',
            'categoria_libreta_militar' => 'required|in:PRIMERA,SEGUNDA,NO_TIENE',
            'numero_libreta_militar' => 'required_if:categoria_libreta_militar,PRIMERA,SEGUNDA',
            'numero_distrito_militar' => 'required_if:categoria_libreta_militar,PRIMERA,SEGUNDA',
            'pais' => 'required',
            'departamento_residencia' => 'required',
            'ciudad_residencia' => 'required',
            'direccion_residencia' => 'required',
            'barrio' => 'required',
            'telefono_movil' => 'required',
            'celular_alternativo' => 'required',
            'correo_alterno' => 'required',

        ]);

        //Si la validación falla, se devuelve un mensaje de error

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // crear un registro de información de contacto
        $informacionContacto = InformacionContacto::create([
            'id_usuario' => $request->id_usuario,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'celular' => $request->celular,
            'ciudad' => $request->ciudad,
            'departamento' => $request->departamento,
            'pais' => $request->pais,
        ]);

        //Devolver respuesta con el registro creado
        return response()->json($informacionContacto, 201);


    }

    public function obtenerInformacionContacto(Request $request)
    {

    }


    public function actualizarInformacionContacto(Request $request)
    {

    }

}
