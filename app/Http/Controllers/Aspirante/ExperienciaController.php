<?php

namespace App\Http\Controllers\Aspirante;


use App\Http\Requests\RequestAspirante\RequestExperiencia\ActualizarExperienciaRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Experiencia;
use App\Models\Aspirante\Documento;// Importar el modelo Documento
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;
use App\Http\Requests\RequestAspirante\RequestExperiencia\CrearExperienciaRequest;

class ExperienciaController
{
    // Crear un registro de experiencia
    public function crearExperiencia(CrearExperienciaRequest $request)
    {
        try {
            $experiencia = DB::transaction(function () use ($request) {
                // Validar los datos de la experiencia
                $datosExperiencia = $request->validated();

                //crear experiencia
                $experiencia = Experiencia::create($datosExperiencia);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Experiencias', $nombreArchivo, 'public');

                    // Guardar el documento relacionado con la experiencia
                    Documento::create([
                        'user_id'        => $request->user()->id, // Usuario autenticado
                        'archivo'        => str_replace('public/', '', $rutaArchivo),
                        'estado'         => 'pendiente',
                        'documentable_id' => $experiencia->id_experiencia, // Relación polimórfica
                        'documentable_type' => Experiencia::class,
                    ]);
                }

                return $experiencia;
            });

            return response()->json([
                'message' => 'Experiencia creada exitosamente',
                'data'    => $experiencia
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la experiencia o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    
    // Obtener todos los registros de experiencia
    public function obtenerExperiencias(Request $request)
    {
        try {
            $user = $request->user(); // Obtiene el usuario autenticado

            // Verificar si el usuario está autenticado
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            // Obtener solo las experiencias que tienen documentos pertenecientes al usuario autenticado
            $experiencias = Experiencia::whereHas('documentosExperiencia', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['documentosExperiencia' => function ($query) {
                $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
            }])
            ->orderBy('created_at')
            ->get();

            // Verificar si se encontraron experiencias
            if ($experiencias->isEmpty()) {
                throw new \Exception ('No se encontraron experiencias', 404);
            }

            // Agregar la URL del archivo a cada documento si existe
            $experiencias->each(function ($experiencia) {
                $experiencia->documentosExperiencia->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });

            return response()->json(['experiencias' => $experiencias], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las experiencias.',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    
    // Obtener un registro de experiencia por ID
    public function obtenerExperienciasPorId(Request $request, $id)
    {
        try {
            $user = $request->user(); // Obtiene el usuario autenticado

            // Verificar si el usuario está autenticado
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            // Obtener solo las experiencias que tienen documentos pertenecientes al usuario autenticado
            $experiencia = Experiencia::where('id_experiencia', $id) // Asegurar que use la clave primaria id_experiencia
            ->whereHas('documentosExperiencia', function ($query) use ($user) {
                $query->where('user_id', $user->id);
                
            })->with(['documentosExperiencia' => function ($query) {
                $query->select('id_documento', 'documentable_id', 'archivo', 'user_id'); // Relación polimórfica usa documentable_id
            }])
            ->orderBy('created_at')
            ->first();

            // Verificar si se encontraron experiencias
            if ($experiencia->isEmpty()) {
                throw new \Exception ('No se encontraron experiencias', 404);
            }

            // Agregar la URL del archivo a cada documento si existe
                $experiencia->documentosExperiencia->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });

            return response()->json(['experiencias' => $experiencia], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la experiencia.',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
    

    
    // Actualizar un registro de experiencia
    public function actualizarExperiencia(ActualizarExperienciaRequest $request, $id)
    {
        try {
            $experiencia = DB::transaction(function () use ($request, $id) {
                $user = $request->user();

                // Buscar la experiencia que tenga documentos del usuario autenticado
                $experiencia = Experiencia::whereHas('documentosExperiencia', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->where('id_experiencia', $id)->firstOrFail(); // Asegurar que use la clave primaria id_experiencia

                // Validar solo los campos que se envían en la solicitud
                $datosExperienciaActualizar = $request->validated();

                // Actualizar la experiencia
                $experiencia->update($datosExperienciaActualizar);

                // Manejo del archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Experiencias', $nombreArchivo, 'public');

                    // Buscar el documento asociado
                    $documento = Documento::where('documentable_id', $experiencia->id_experiencia)
                        ->where('documentable_type', Experiencia::class)
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
                            'documentable_id' => $experiencia->id_experiencia,
                            'documentable_type' => Experiencia::class,
                        ]);
                    }
                }
                return $experiencia;
            });

            return response()->json([
                'message' => 'Experiencia actualizada correctamente',
                'data'    => $experiencia->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la experiencia o manejar el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    


    // Eliminar un registro de experiencia
    public function eliminarExperiencia(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Buscar la experiencia que tenga documentos del usuario autenticado
            $experiencia = Experiencia::whereHas('documentosExperiencia', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('id_experiencia', $id)->firstOrFail(); // Asegurar que use la clave primaria id_experiencia

            if(!$experiencia) {
                return response()->json(['message' => 'Experiencia no encontrada'], 404);
            }

            // Eliminar el documento asociado
            DB::transaction(function () use ($experiencia){
                foreach ($experiencia->documentosExperiencia as $documento) {
                    // Eliminar el archivo del almacenamiento si existe
                    if (!empty($documento->archivo) && Storage::exists('public/' . $documento->archivo)) {
                        Storage::delete('public/' . $documento->archivo);
                    }
                    $documento->delete(); // Eliminar el documento de la base de datos
                }
                $experiencia->delete();
            });

            // Eliminar la experiencia
            $experiencia->delete();

            return response()->json(['message' => 'Experiencia eliminada correctamente'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la experiencia.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}