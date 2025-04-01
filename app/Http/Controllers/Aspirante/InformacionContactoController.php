<?php

namespace App\Http\Controllers\Aspirante;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\InformacionContacto;
use App\Constants\ConstInformacionContacto\CategoriaLibretaMilitar;


class InformacionContactoController
{

    //Crear un registro de información de contacto
    public function crearInformacionContacto(Request $request)
    {
        //Validar los datos de entrada

        $validator = Validator::make(request()->all(), [

            'categoria_libreta_militar'             => 'nullable|in:' . implode(',', CategoriaLibretaMilitar::all()),//llamo a la constante categoria libreta militar para obtener los tipos de libreta militar
            'numero_libreta_militar'                => 'nullable|string|max:50',
            'numero_distrito_militar'               => 'nullable|string|max:50',
            'direccion_residencia'                  => 'nullable|string|max:100',
            'barrio'                                => 'nullable|string|max:100',
            'telefono_movil'                        => 'required|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'celular_alternativo'                   => 'nullable|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'correo_alterno'                        => 'nullable|string|email|max:100|unique:users,email',
            
        ]);

        //Si la validación falla, se devuelve un mensaje de error

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // crear un registro de información de contacto
        $informacionContacto = InformacionContacto::create([
            'municipio_id'              => $request->input('municipio_id'),
            'categoria_libreta_militar' => $request->input('categoria_libreta_militar'),
            'numero_libreta_militar'    => $request->input('numero_libreta_militar'),
            'numero_distrito_militar'   => $request->input('numero_distrito_militar'),
            'municipio_id'              => $request->input('municipio_id'),
            'direccion_residencia'      => $request->input('direccion_residencia'),
            'barrio'                    => $request->input('barrio'),
            'telefono_movil'            => $request->input('telefono_movil'),
            'celular_alternativo'       => $request->input('celular_alternativo'),
            'correo_alterno'            => $request->input('correo_alterno'),
        ]);

        //Devolver respuesta con el registro creado
        return response()->json($informacionContacto, 201);
    }


       
    //Obtener la información de contacto del usuario autenticado
    public function obtenerInformacionContacto(Request $request) {

        //Buscar la información de contacto del usuario autenticado
        $informacionContacto = InformacionContacto::where('user_id', $request->user()->id)->first();

        //Si no se encuentra la información de contacto, se devuelve un mensaje de error
        if($informacionContacto->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'No tiene información de contacto registrada'
            ], 404);
        }

        //Devolver la información de contacto encontrada
        return response()->json([
            'mensagge' => 'Información de contacto obtenida exitosamente',
            'data'    => $informacionContacto
        ], 200);
    }



    //Actualizar la información de contacto del usuario autenticado
    public function actualizarInformacionContacto(Request $request,) {
        

        //Buscar la información de contacto del usuario autenticado
        $informacionContacto = InformacionContacto::where('user_id', $request->user()->id)->first();

        //Si no se encuentra la información de contacto, se devuelve un mensaje de error
        if (!$informacionContacto) {
            return response()->json(['message' => 'Información de contacto no encontrada'], 404);
        }

        //Validar los datos de entrada
        $validator = Validator::make(request()->all(), [

            'municipio_id'                          => 'sometimes|exists:municipios,id',
            'categoria_libreta_militar'             => 'sometimes|nullable|in:' . implode(',', CategoriaLibretaMilitar::all()),
            'numero_libreta_militar'                => 'sometimes|nullable|string|max:50',
            'numero_distrito_militar'               => 'sometimes|nullable|string|max:50',
            'direccion_residencia'                  => 'sometimes|nullable|string|max:100',
            'barrio'                                => 'sometimes|nullable|string|max:100',
            'telefono_movil'                        => 'sometimes|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'celular_alternativo'                   => 'sometimes|nullable|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'correo_alterno'                        => 'sometimes|nullable|string|email|max:100|unique:users,email',
            
        ]);

        //Si la validación falla, se devuelve un mensaje de error
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        //Actualizar la información de contacto
        $informacionContacto->update($request->only([
            'municipio_id',
            'categoria_libreta_militar',
            'numero_libreta_militar',
            'numero_distrito_militar',
            'direccion_residencia',
            'barrio',
            'telefono_movil',
            'celular_alternativo',
            'correo_alterno',
        ]));

        //Devolver respuesta con la información de contacto actualizada
        return response()->json([
            'message' => 'Información de contacto actualizada exitosamente',
            'data'    => $informacionContacto->fresh()
        ], 200);
    }
}