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
                $datosEstudio = $request->validated();

                // Agregar el user_id del usuario autenticado
                $datosEstudio['user_id'] = $request->user()->id;

                // Crear el registro de estudio con user_id
                $estudio = Estudio::create($datosEstudio);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Estudios', $nombreArchivo, 'public');

                    Documento::create([
                        'archivo'          => str_replace('public/', '', $rutaArchivo),
                        'estado'           => 'pendiente',
                        'documentable_id'  => $estudio->id_estudio,
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
            $user = $request->user(); // Usuario autenticado

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            // Obtener estudios directamente por user_id
            $estudios = Estudio::where('user_id', $user->id)
                ->with(['documentosEstudio' => function ($query) {
                    $query->select('id_documento', 'documentable_id', 'archivo', 'estado');
                }])
                ->orderBy('created_at')
                ->get();

            if ($estudios->isEmpty()) {
                throw new \Exception('No se encontraron estudios', 404);
            }

            // Agregar URL del archivo si existe
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
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Obtener un estudio por ID
    public function obtenerEstudioPorId(Request $request, $id)
    {
        try {
            $user = $request->user(); // Usuario autenticado

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            // Obtener el estudio directamente por su id y por el user_id
            $estudio = Estudio::where('id_estudio', $id)
                ->where('user_id', $user->id)
                ->with(['documentosEstudio' => function ($query) {
                    $query->select('id_documento', 'documentable_id', 'archivo', 'estado');
                }])
                ->first();

            if (!$estudio) {
                throw new \Exception('Estudio no encontrado', 404);
            }

            // Adjuntar la URL del archivo a cada documento si existe
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

                // Buscar el estudio directamente con user_id e id_estudio
                $estudio = Estudio::where('id_estudio', $id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                // Validar y actualizar los datos
                $datosEstudioActualizar = $request->validated();
                $estudio->update($datosEstudioActualizar);

                // Si hay archivo nuevo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Estudios', $nombreArchivo, 'public');

                    // Buscar documento asociado al estudio
                    $documento = Documento::where('documentable_id', $estudio->id_estudio)
                        ->where('documentable_type', Estudio::class)
                        ->first();

                    if ($documento) {
                        // Eliminar archivo anterior del storage
                        Storage::disk('public')->delete($documento->archivo);

                        $documento->update([
                            'archivo' => str_replace('public/', '', $rutaArchivo),
                            'estado'  => 'pendiente',
                        ]);
                    } else {
                        // Crear nuevo documento si no existe
                        Documento::create([
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

            // Buscar el estudio directamente por user_id
            $estudio = Estudio::where('id_estudio', $id)
                ->where('user_id', $user->id)
                ->with('documentosEstudio')
                ->first();

            if (!$estudio) {
                return response()->json(['error' => 'Estudio no encontrado o no tienes permiso para eliminarlo'], 403);
            }

            DB::transaction(function () use ($estudio) {
                foreach ($estudio->documentosEstudio as $documento) {
                    if (!empty($documento->archivo) && Storage::disk('public')->exists($documento->archivo)) {
                        Storage::disk('public')->delete($documento->archivo);
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
