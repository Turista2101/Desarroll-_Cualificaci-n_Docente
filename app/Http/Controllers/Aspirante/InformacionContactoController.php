<?php

namespace App\Http\Controllers\Aspirante;

use Illuminate\Http\Request;
use App\Models\Aspirante\InformacionContacto;
use App\Http\Requests\RequestAspirante\RequestInformacionContacto\ActualizarInformacionContactoRequest;
use App\Http\Requests\RequestAspirante\RequestInformacionContacto\CrearInformacionContactoRequest;
use App\Models\Aspirante\Documento; // Importar el modelo Documento
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InformacionContactoController
{


    //Crear un registro de información de contacto
    public function crearInformacionContacto(CrearInformacionContactoRequest $request)
    {
        try {
            $informacionContacto= DB::transaction(function () use ($request) {
                // Validar los datos de la solicitud
                $datosInfomacionContacto = $request->validated();

                $datosInfomacionContacto['user_id'] = $request->user()->id;
    
                // Crear información de contacto
                $informacionContacto = InformacionContacto::create($datosInfomacionContacto);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/LibretaMilitar', $nombreArchivo, 'public');

                    //Guardar el documento relacionado con la información de contacto
                    Documento::create([
                        'user_id' => $request->user()->id,
                        'archivo' => str_replace('public/', '', $rutaArchivo),
                        'estado'  => 'pendiente',
                        'documentable_id' => $informacionContacto->id_informacion_contacto,
                        'documentable_type' => InformacionContacto::class,
                    ]);
                }

                return $informacionContacto;
            });
            // Devolver respuesta con la información de contacto creada
            return response()->json([
                'message' => 'Información de contacto y documento guardados correctamente',
                'data'    => $informacionContacto
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la información de contacto o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    


    // Obtener la información de contacto del usuario autenticado
    public function obtenerInformacionContacto(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();

            // verificar si el usuario esta autenticado
            if (!$user) {
                throw new \Exception ('Usuario no autenticado', 401);
            }

            //obtener solo los estudios que tiene documentos pertenecientes al usuario autenticado
            $informacionContacto = InformacionContacto::where('user_id', $user->id)
            ->with(['documentosInformacionContacto' => function ($query) {
                $query->select('id_documento', 'documentable_id', 'archivo', 'estado', );
            }])->first();

            //verificar si el usuario tiene información de contacto
            if (!$informacionContacto) {
                throw new \Exception('No se encontró información de contacto', 404);
            }
            //Agregar la URL del archivo a cada documento si existe
            foreach ($informacionContacto->documentosInformacionContacto as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }

            return response()->json(['informacion_contacto' => $informacionContacto], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la información de contacto',
                'error'   => $e->getMessage()
            ],$e->getCode() ?: 500);
        }
    }



    //Actualizar información de contacto
    public function actualizarInformacionContacto(ActualizarInformacionContactoRequest $request)
    {
        try{
            $informacionContacto= DB::transaction(function () use ($request) {
                // Obtener el usuario autenticado
                $user = $request->user();
                
                $informacionContacto = InformacionContacto::where('user_id', $user->id)->firstOrFail();
                // Verificar si el usuario está autenticado
                // Buscar el registro de información de contacto por ID
                $datosInfomacionContactoActualizar = $request->validated();

                // Actualizar solo los campos que se envían en la solicitud
                $informacionContacto->update($datosInfomacionContactoActualizar);
        
                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/LibretaMilitar', $nombreArchivo, 'public');
            
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
                            'archivo'          => str_replace('public/', '', $rutaArchivo),
                            'estado'           => 'pendiente',
                            'documentable_id'  => $informacionContacto->id_informacion_contacto,
                            'documentable_type' => InformacionContacto::class,
                        ]);
                    }
                }

                 return $informacionContacto;
            });
            
            // Devolver respuesta con la información de contacto actualizada
            return response()->json([
                    'message' => 'Información de contacto actualizada correctamente',
                    'data'    => $informacionContacto->fresh() // Obtener la información actualizada
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la información de contacto o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}