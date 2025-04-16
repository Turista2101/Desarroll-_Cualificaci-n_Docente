<?php

namespace App\Http\Controllers\Aspirante;

use App\Http\Requests\RequestAspirante\RequestRut\ActualizarRutRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Rut;
use App\Models\Aspirante\Documento;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RequestAspirante\RequestRut\CrearRutRequest;
use Illuminate\Support\Facades\DB;


class RutController
{
    //Crear un nuevo registro de rut
    public function crearRut(CrearRutRequest $request)
    {
        try {
            $rut = DB::transaction(function () use ($request) {
                $datosRut = $request->validated();

                $rut = Rut::create($datosRut);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Rut', $nombreArchivo, 'public');

                // Guardar el documento relacionado con el rut
                    Documento::create([
                        'user_id'   => $request->user()->id,
                        'archivo'   => str_replace('public/', '', $rutaArchivo),
                        'estado'    => 'pendiente',
                        'documentable_id' => $rut->id_rut,
                        'documentable_type' => Rut::class,

                    ]);
                }
                return $rut;
            });

            return response()->json([
                'message' => 'Rut creado exitosamente',
                'data'     => $rut,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la EPS o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    //obtener estudios del usuario autenticado
    public function obtenerRut(Request $request)
    {
        try{
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            // Obtener solo los estudios que tienen documentos pertenecientes al usuario autenticado
            $ruts = Rut::whereHas('documentosRut', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['documentosRut' => function ($query) {
                $query->select('id_documento', 'documentable_id', 'archivo', 'user_id');
            }])->first();

            if (!$ruts) {
                throw new \Exception('No se encontró información de RUT', 404);
            }
            
            //Agregar la URL del archivo a cada documento si existe
            foreach ($ruts->documentosRut as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }
            
            return response()->json(['ruts' => $ruts], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la información de RUT',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    //actualizar rut
    public function actualizarRut(ActualizarRutRequest $request)
    {
        try {
            $rut =DB::transaction(function () use ($request) {
                    
                $user = $request->user();

                // Buscar el estudio que tenga documentos del usuario autenticado
                $rut = Rut::whereHas('documentosRut', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->firstOrFail(); // Asegurar que use la clave primaria id_estudio

                $datosRutActualizar = $request->validated();

                $rut->update($datosRutActualizar);
                // Validar solo los campos que se envían en la solicitud

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
                            'documentable_type' => Rut::class,
                        ]);
                    }
                }
                return $rut;
            });
            return response()->json([
                'message' => 'Rut actualizado correctamente',
                'data'    => $rut->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el RUT',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


}
