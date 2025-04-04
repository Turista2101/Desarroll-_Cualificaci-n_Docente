<?php

namespace App\Http\Controllers\Aspirante;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\InformacionContacto;
use App\Constants\ConstInformacionContacto\CategoriaLibretaMilitar;
use App\Constants\ConstInformacionContacto\TipoIdentificacion;
use App\Models\Aspirante\Documento; // Importar el modelo Documento
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InformacionContactoController
{


    //Crear un registro de información de contacto
    public function crearInformacionContacto(Request $request)
    {
        //Validar los datos de entrada

        $validator = Validator::make(request()->all(), [
            'municipio_id'                          => 'required|exists:municipios,id',
            'tipo_identificacion'                   => 'required|in:' . implode(',', TipoIdentificacion::all()),// llamo a la constante TipoIdentificacion para obtener los tipos de identificacion
            'numero_identificacion'                 => 'required|string|max:50',
            'categoria_libreta_militar'             => 'nullable|in:' . implode(',', CategoriaLibretaMilitar::all()),//llamo a la constante categoria libreta militar para obtener los tipos de libreta militar
            'numero_libreta_militar'                => 'nullable|string|max:50',
            'numero_distrito_militar'               => 'nullable|string|max:50',
            'direccion_residencia'                  => 'nullable|string|max:100',
            'barrio'                                => 'nullable|string|max:100',
            'telefono_movil'                        => 'required|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'celular_alternativo'                   => 'nullable|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'correo_alterno'                        => 'nullable|string|email|max:100|unique:users,email',
            'archivo'                               => 'required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
            
        ]);

        //Si la validación falla, se devuelve un mensaje de error

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // crear un registro de información de contacto
        $informacionContacto = InformacionContacto::create([
            'tipo_identificacion'       => $request->input('tipo_identificacion'),
            'numero_identificacion'     => $request->input('numero_identificacion'),
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

        // Verificar si se envió un archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('documentos/Indentificacion', $nombreArchivo, 'public');

            //Guardar el documento relacionado con la información de contacto
            Documento::create([
                'user_id' => $request->user()->id,
                'archivo' => str_replace('public/', '', $rutaArchivo),
                'estado'  => 'pendiente',
                'documentable_id' => $informacionContacto->id_informacion_contacto,
                'documentable_type' => InformacionContacto::class,
            ]);
        }

        // Devolver respuesta con la información de contacto creada
        return response()->json([
            'message' => 'Información de contacto y documento guardados correctamente',
            'data'    => $informacionContacto
        ], 201);
        
    }





    // Obtener la información de contacto del usuario autenticado
    public function obtenerInformacionContacto(Request $request)
    {
        // Obtener el usuario autenticado
        $user = $request->user();

        // verificar si el usuario esta autenticado
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        //obtener solo los estudios que tiene documentos pertenecientes al usuario autenticado
        $informacionContacto = InformacionContacto::whereHas('documentosInformacionContacto', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['documentosInformacionContacto' => function ($query) {
            $query->select('id_documento', 'documentable_id', 'archivo', 'user_id', );
        }])->first();

        //Agregar la URL del archivo a cada documento si existe
        $informacionContacto->each(function ($informacionContacto) {
            $informacionContacto->documentosInformacionContacto->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });
        });

        return response()->json(['informacion_contacto' => $informacionContacto], 200);
    }

    //Actualizar información de contacto
    public function actualizarInformacionContacto(Request $request, $id)
    {
        // Obtener el usuario autenticado
        $user = $request->user();
        // Buscar el registro de información de contacto por ID
        $informacionContacto = InformacionContacto::whereHas('documentosInformacionContacto', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id_informacion_contacto', $id)->firstOrFail();
       
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'municipio_id'                          => 'required|exists:municipios,id',
            'tipo_identificacion'                   => 'required|in:' . implode(',', TipoIdentificacion::all()),
            'numero_identificacion'                 => 'required|string|max:50',
            'categoria_libreta_militar'             => 'nullable|in:' . implode(',', CategoriaLibretaMilitar::all()),
            'numero_libreta_militar'                => 'nullable|string|max:50',
            'numero_distrito_militar'               => 'nullable|string|max:50',
            'direccion_residencia'                  => 'nullable|string|max:100',
            'barrio'                                => 'nullable|string|max:100',
            'telefono_movil'                        => 'required|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'celular_alternativo'                   => 'nullable|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'correo_alterno'                        => 'nullable|string|email|max:100|unique:users,email',
            'archivo'                               => 'sometimes|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
            // Agregar validación para el archivo si es necesario
        ]);

        // Si la validación falla, devolver un error
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }


       // Actualizar los datos directamente
         $data=$request->only([
                'tipo_identificacion',
                'numero_identificacion',
                'municipio_id',
                'categoria_libreta_militar',
                'numero_libreta_militar',
                'numero_distrito_militar',
                'direccion_residencia',
                'barrio',
                'telefono_movil',
                'celular_alternativo',
                'correo_alterno'
          ]);
            
          $informacionContacto->update($data);
    
          // Verificar si se envió un archivo
          if ($request->hasFile('archivo')) {
                $archivo = $request->file('archivo');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $rutaArchivo = $archivo->storeAs('documentos/Indentificacion', $nombreArchivo, 'public');
    
                // Buscar el documento asociado
                $documento = Documento::where('documentable_id', $informacionContacto->id_informacion_contacto)
                    ->where('documentable_type', InformacionContacto::class)
                    ->first();
                // Si existe, actualizarlo
                // Si no existe, crear uno nuevo
                if ($documento) {
                    Storage::disk('public')->delete($documento->archivo);
                    $documento->update([
                        'archivo' => str_replace('public/', '', $rutaArchivo),
                        'estado'  => 'pendiente',
                    ]);
                } else {
                    Documento::create([
                        'user_id'          => $user->id,
                        'archivo'          => str_replace('public/', '', $rutaArchivo),
                        'estado'           => 'pendiente',
                        'documentable_id'  => $informacionContacto->id_informacion_contacto,
                        'documentable_type' => InformacionContacto::class,
                    ]);
                }
          }
    
          // Devolver respuesta con la información de contacto actualizada
          return response()->json([
                'message' => 'Información de contacto actualizada correctamente',
                'data'    => $informacionContacto
          ], 200);
    }


}