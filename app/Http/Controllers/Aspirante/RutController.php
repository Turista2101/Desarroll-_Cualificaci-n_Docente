<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstRut\CodigoCiiu;
use App\Constants\ConstRut\TipoPersona;
use  Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Rut;
use App\Models\Aspirante\Documento;


class RutController
{
    //Crear un nuevo registro de rut
    public function cerarRut(Request $request)
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
}


    //Obtener la información de rut del usuario autenticado
    


    
    //Actualizar la información de rut del usuario autenticado
    




    

}
