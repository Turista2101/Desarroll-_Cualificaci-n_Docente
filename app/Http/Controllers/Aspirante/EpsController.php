<?php
namespace App\Http\Controllers\Aspirante;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Aspirante\Eps;
use App\Constants\ConstEps\TipoAfiliacion;
use App\Constants\ConstEps\EstadoAfiliacion;
use App\Constants\ConstEps\TipoAfiliado;
use App\Models\Aspirante\Documento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


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
    public function obtenerEps(Request $request)
    {
        // Obtener el usuario autenticado
        $user = $request->user();

        // verificar si el usuario esta autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }
        //obtener solo los estudios que tiene documentos pertenecientes al usuario autenticado
        $eps = Eps::whereHas ('documentos', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['documentos'=>function ($query) {
            $query->select('id_documento','documentable_id','archivo','user_id','estado');
        }])->first();

        //Agregar la URL del archivo a cada documento si existe
        $eps->each(function ($eps){
            $eps->documentos->each(function($documento){
                if(!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
            }
        }); 
    });
    return response()->json(['eps'=>$eps], 200);

    }

    //actualizar eps
    public function actualizarEps(Request $request,$id){
        $user = $request->user();
        // Buscar el estudio que tenga documentos del usuario autenticado
        $eps = Eps::whereHas('documentosEps', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id_estudio', $id)->firstOrFail();
        // Validar solo los campos que se envían en la solicitud
        $validator = Validator::make($request->all(), [
         'nombre_eps'                    => 'sometimes|required|string|min:7|max:100',
            'tipo_afiliacion'               => 'sometimes|required|in:' . implode(',', TipoAfiliacion::all()),
            'estado_afiliacion'             => 'sometimes|required|in:' . implode(',', EstadoAfiliacion::all()),
            'fecha_afiliacion_efectiva'     => 'sometimes|required|date',
            'fecha_finalizacion_afiliacion' => 'sometimes|nullable|date',
            'tipo_afiliado'                 => 'sometimes|required|in:' . implode(',', TipoAfiliado::all()),
            'numero_afiliado'               => 'sometimes|nullable|string|max:100',
            'archivo'                       => 'sometimes|required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        //acrualizar los datos directamente
        $data=$request->only([
            'nombre_eps',
            'tipo_afiliacion',
            'estado_afiliacion',
            'fecha_afiliacion_efectiva',
            'fecha_finalizacion_afiliacion',
            'tipo_afiliado',
            'numero_afiliado'
        ]);
        $eps->update($data);
        //Manejo del archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('public/documentos/Eps', $nombreArchivo,'public');
            
            // Buscar el doumento asociado
            $documento = Documento::where('documentable_id', $eps->id_eps)
                ->where('documentable_type', Eps::class)
                ->where('user_id', $user->id)
                ->first();

            if ($documento){
                Storage::disk('public')->delete($documento->archivo);
                $documento->update([
                    'archivo' => str_replace('public/', '', $rutaArchivo),
                    'estado'  => 'pendiente',
                ]);
            }else{
                // Guardar el documento relacionado con el eps
                Documento::create([
                    'user_id'          => $user->id,
                    'archivo'          => str_replace('public/', '', $rutaArchivo),
                    'estado'           => 'pendiente',
                    'documentable_id'  => $eps->id_eps,
                    'documentable_type' => Eps::class,
                ]);
            }
        }
        return response()->json([
            'message' => 'Eps actualizado exitosamente',
            'data'    => $eps->fresh() // Obtener la instancia actualizada
        ], 200);
    }
   








}
