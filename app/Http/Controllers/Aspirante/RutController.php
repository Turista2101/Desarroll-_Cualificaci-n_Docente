<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstRut\CodigoCiiu;
use App\Constants\ConstRut\TipoPersona;
use  Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Rut;
use App\Models\Aspirante\Documento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;



class RutController
{
    //Crear un nuevo registro de rut
    public function crearRut(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'nombre_rut'                    => 'required|string|min:7|max:100',
            'razon_social'                  => 'required|string|min:7|max:100',
            'tipo_persona'                  => 'required|in:' . implode(',', TipoPersona::all()),
            'codigo_ciiu'                   => 'required|in:' . implode(',', CodigoCiiu::all()),
            'Responsabilidades_tributarias' => 'required|string|min:7|max:100',
            'archivo'                       => 'required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $rut = Rut::create([
            'nombre_rut'                    => $request->input('nombre_rut'),
            'razon_social'                  => $request->input('razon_social'),
            'tipo_persona'                  => $request->input('tipo_persona'),
            'codigo_ciiu'                   => $request->input('codigo_ciiu'),
            'Responsabilidades_tributarias' => $request->input('Responsabilidades_tributarias'),

        ]);
        
        // Verificar si se envió un archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('public/documentos/Rut', $nombreArchivo);

            // Guardar el documento relacionado con el rut
            Documento::create([
                'user_id'   => $request->user()->id,
                'archivo'   => str_replace('public/', 'storage/', 'Rut/', $rutaArchivo),
                'documentable_id' => $rut->id_rut,
                'documentable_type' => Rut::class,

            ]);
          
    }
        return response()->json([
            'message' => 'Rut creado exitosamente',
            'data'     => $rut,
        ], 201);
    }

    
    //obtener estudios del usuario autenticado
    public function obtenerRut(Request $request)
    {
           $user = $request->user();
           if(!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Obtener solo los estudios que tienen documentos pertenecientes al usuario autenticado
        $ruts = Rut::whereHas('documentosRut', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['documentosRut' => function ($query)  {
            $query->select('id_documento', 'documentable_id', 'archivo', 'user_id');
        }])->get();

        //Agregar la URL del archivo a cada documento si existe
        $ruts->each(function ($rut) {
            $rut->documentosRut->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });
        });
        return response()->json(['ruts' => $ruts], 200);
}

//actualizar rut
public function actualizarRut(Request $request, $id)
{
    $user = $request->user();

    // Buscar el estudio que tenga documentos del usuario autenticado
    $rut = Rut::whereHas('documentosRut', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->where('id_rut', $id)->firstOrFail(); // Asegurar que use la clave primaria id_estudio

    // Validar solo los campos que se envían en la solicitud
    $validator = Validator::make($request->all(), [
        'nombre_rut'                    => 'sometimes|required|string|min:7|max:100',
        'razon_social'                  => 'sometimes|required|string|min:7|max:100',
        'tipo_persona'                  => 'sometimes|required|in:' . implode(',', TipoPersona::all()),
        'codigo_ciiu'                   => 'sometimes|required|in:' . implode(',', CodigoCiiu::all()),
        'Responsabilidades_tributarias' => 'sometimes|required|string|min:7|max:100',
        'archivo'                       => 'sometimes|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
    ]);
    if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
    }
    //Obtener la información de rut del usuario autenticado
    Log::info('Datos recibidos para actualización:', $request->all());
    Log::info('Datos actuales del modelo:', $rut->toArray());
    $data = $request->only([
        'nombre_rut',
        'razon_social',
        'tipo_persona',
        'codigo_ciiu',
        'Responsabilidades_tributarias',
    ]);
    $rut->update($data);

        // Manejo del archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('documentos/Rut', $nombreArchivo, 'public');

            // Buscar el documento asociado
            $documento = Documento::where('documentable_id', $rut->id_rut)
                ->where('documentable_type', Rut::class)
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
                    'documentable_id' => $rut->id_rut,
                    'documentable_type' =>Rut::class,
                ]);
            }
        }

        return response()->json([
            'message' => 'Rut actualizado correctamente',
            'data'    => $rut->refresh()
        ], 200);


    
    




    

}
}