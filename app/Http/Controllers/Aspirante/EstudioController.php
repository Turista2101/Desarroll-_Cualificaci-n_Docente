<?php

namespace App\Http\Controllers\Aspirante;


use App\Http\Requests\RequestAspirante\RequestEstudio\ActualizarEstudioRequest;
use App\Http\Requests\RequestAspirante\RequestEstudio\CrearEstudioRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Estudio;
use App\Models\Aspirante\Documento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EstudioController
{
    //crear un registro de estudio
    public function crearEstudio(CrearEstudioRequest $request)
    {
        try {
            $estudio = DB::transaction(function () use ($request) {
                // Validar los datos de la solicitud
                $datosEstudio = $request->validated();

                // Crear el registro de estudio
                $estudio = Estudio::create($datosEstudio);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Estudios', $nombreArchivo, 'public');

                    // Guardar el documento relacionado con el estudio
                    Documento::create([
                        'user_id'        => $request->user()->id, // Usuario autenticado
                        'archivo'        => str_replace('public/', '', $rutaArchivo),
                        'estado'         => 'pendiente',
                        'documentable_id' => $estudio->id_estudio, // Relación polimórfica
                        'documentable_type' => Estudio::class,
                    ]);
                }

                return $estudio;
            });

            return response()->json([
                'message' => 'Estudio y documento creados exitosamente',
                'data'    => $estudio,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el estudio o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    // Obtener estudios del usuario autenticado
    public function obtenerEstudios(Request $request)
    {
        try {
            $user = $request->user(); // Obtiene el usuario autenticado
    
            // Verificar si el usuario está autenticado
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
    
            // Obtener solo los estudios que tienen documentos pertenecientes al usuario autenticado
            $estudios = Estudio::whereHas('documentosEstudio', function ($query) use ($user) {
                $query->where('user_id', $user->id);

            })->with(['documentosEstudio' => function ($query) {
                $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
            }])
            ->orderBy('created_at') // Ordenar por fecha de creación descendente
            ->get();

            // Verificar si se encontraron estudios
            if ($estudios->isEmpty()) {
                throw new \Exception('No se encontraron estudios', 404);
            }
    
            // Agregar la URL del archivo a cada documento si existe
            $estudios->each(function ($estudio) {
                $estudio->documentosEstudio->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });
    
            return response()->json(['estudios' => $estudios], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los estudios',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    public function obtenerEstudioPorId(Request $request, $id)
    {
        try {
            $user = $request->user(); // Obtiene el usuario autenticado

            // Verificar si el usuario está autenticado
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            // Obtener el estudio por ID, asegurando que tenga documentos del usuario autenticado
            $estudio = Estudio::where('id_estudio', $id)
                ->whereHas('documentosEstudio', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['documentosEstudio' => function ($query) {
                    $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
                }])
                ->orderBy('created_at') // Ordenar por fecha de creación
                ->first();

            // Verificar si se encontró el estudio
            if (!$estudio) {
                throw new \Exception('Estudio no encontrado', 404);
            }

            // Agregar la URL del archivo a cada documento si existe
            $estudio->documentosEstudio->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });

            return response()->json(['estudio' => $estudio], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el estudio',
                'error'   => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }



    // Actualizar estudio
    public function actualizarEstudio(ActualizarEstudioRequest $request, $id)
    {
        try {
            $estudio = DB::transaction(function () use ($request, $id) {
                $user = $request->user();
    
                // Buscar el estudio del usuario autenticado
                $estudio = Estudio::whereHas('documentosEstudio', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->where('id_estudio', $id)->firstOrFail();
    
                // Validar y actualizar
                $datosEstudioActualizar = $request->validated();
                $estudio->update($datosEstudioActualizar);
    
                // Si hay archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Estudios', $nombreArchivo, 'public');
    
                    // Buscar documento relacionado
                    $documento = Documento::where('documentable_id', $estudio->id_estudio)
                        ->where('documentable_type', Estudio::class)
                        ->where('user_id', $user->id)
                        ->first();
    
                    if ($documento) {
                        // Eliminar archivo anterior
                        Storage::disk('public')->delete($documento->archivo);
    
                        $documento->update([
                            'archivo' => str_replace('public/', '', $rutaArchivo),
                            'estado'  => 'pendiente',
                        ]);
                    } else {
                        Documento::create([
                            'user_id'           => $user->id,
                            'archivo'           => str_replace('public/', '', $rutaArchivo),
                            'estado'            => 'pendiente',
                            'documentable_id'   => $estudio->id_estudio,
                            'documentable_type' => Estudio::class,
                        ]);
                    }
                }
    
                return $estudio;
            });
    
            return response()->json([
                'message' => 'Estudio actualizado correctamente',
                'data'    => $estudio->refresh()
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el estudio',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    

    // Eliminar estudio
    public function eliminarEstudio(Request $request, $id)
    {
        try {
            $user = $request->user(); // Usuario autenticado
    
            $estudio = Estudio::whereHas('documentosEstudio', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('id_estudio', $id)->first();
    
            if (!$estudio) {
                return response()->json(['error' => 'Estudio no encontrado o no tienes permiso para eliminarlo'], 403);
            }
    
            DB::transaction(function () use ($estudio) {
                foreach ($estudio->documentosEstudio as $documento) {
                    if (!empty($documento->archivo) && Storage::exists('public/' . $documento->archivo)) {
                        Storage::delete('public/' . $documento->archivo);
                    }
                    $documento->delete();
                }
    
                $estudio->delete();
            });
    
            return response()->json(['message' => 'Estudio eliminado correctamente'], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el estudio',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}