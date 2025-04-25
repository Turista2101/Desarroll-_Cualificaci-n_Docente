<?php

namespace App\Http\Controllers\TalentoHumano;


use App\Http\Requests\RequestTalentoHumano\RequestConvocatoria\ActualizarConvocatoriaRequest;
use App\Http\Requests\RequestTalentoHumano\RequestConvocatoria\CrearConvocatoriaRequest;
use App\Models\Aspirante\Documento;
use App\Models\TalentoHumano\Convocatoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Notifications\NotificacionGeneral;
use Illuminate\Support\Facades\Notification;
use App\Models\Usuario\User;



class ConvocatoriaController
{
    // Crear un registro de convocatoria
    public function crearConvocatoria(CrearConvocatoriaRequest $request)
    {
        try {
            $convocatoria = DB::transaction(function () use ($request) {
                
                $datosConvocatoria = $request->validated();
                $convocatoria = Convocatoria::create($datosConvocatoria);

                 // Verificar si se envió un archivo
                 if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Convocatorias', $nombreArchivo, 'public');

                    Documento::create([ 
                        'archivo'        => str_replace('public/', '', $rutaArchivo),
                        'documentable_id' => $convocatoria->id_convocatoria, // Relación polimórfica
                        'documentable_type' => Convocatoria::class,
                    ]);
                }
                // // Notificar a usuarios con rol "Usuario" o "aspirante"
                // $usuarios = User::roles(['Aspirante','Docente'])->get();
                // Notification::send($usuarios, new NotificacionGeneral('Nueva convocatoria disponible.'));

                // // Notificar a Talento Humano (opcional)
                // $talentoHumano = User::role('Talento Humano')->get();
                // Notification::send($talentoHumano, new NotificacionGeneral('Convocatoria registrada exitosamente.'));
                
                return $convocatoria;
            });

            return response()->json([
                'mensaje' => 'Convocatoria creada exitosamente',
                'data' => $convocatoria
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear la convocatoria: ',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    // Actualizar un registro de convocatoria
    public function actualizarConvocatoria(ActualizarConvocatoriaRequest $request, $id)
    {
        try {
            $convocatoria = DB::transaction(function () use ($request, $id) {
                // Validar los datos de la solicitud
                $datosConvocatoria = $request->validated();
                 
                //buscar el registro de convocatoria por id
                $convocatoria = Convocatoria::findOrFail($id);

                // Actualizar el registro de convocatoria
                $convocatoria->update($datosConvocatoria);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Convocatorias', $nombreArchivo, 'public');

                    $documento = Documento::where('documentable_id', $convocatoria->id_convocatoria)
                    ->where('documentable_type', Convocatoria::class)
                    ->first();

                    if ($documento) {
                        // Eliminar archivo anterior del storage
                        Storage::disk('public')->delete($documento->archivo);

                        $documento->update([
                            'archivo' => str_replace('public/', '', $rutaArchivo),
                        ]);
                    } else {
                        // Crear nuevo documento si no existe
                        Documento::create([
                            'archivo'           => str_replace('public/', '', $rutaArchivo),
                            'documentable_id'   => $convocatoria->id_convocatoria,
                            'documentable_type' => Convocatoria::class,
                        ]);
                    }
                    
                }
                return $convocatoria;
            });

            return response()->json([
                'mensaje' => 'Convocatoria actualizada exitosamente',
                'data' => $convocatoria->refresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar la convocatoria: ',
                'error'   => $e->getMessage()
            ], 500);
        }

        
    }


    public function eliminarConvocatoria($id)
    {
        try {
            // Buscar la convocatoria por su ID
            $convocatoria = Convocatoria::findOrFail($id);
    
            DB::transaction(function () use ($convocatoria) {
                // Iterar sobre los documentos asociados a la convocatoria
                foreach ($convocatoria->documentosConvocatoria as $documento) {
                    if (!empty($documento->archivo) && Storage::disk('public')->exists($documento->archivo)) {
                        // Eliminar el archivo del almacenamiento
                        Storage::disk('public')->delete($documento->archivo);
                    }
                    // Eliminar el documento de la base de datos
                    $documento->delete();
                }
    
                // Eliminar la convocatoria
                $convocatoria->delete();
            });
    
            return response()->json(['mensaje' => 'Convocatoria eliminada exitosamente'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la convocatoria: ',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    public function obtenerConvocatorias()
    {
        try {
            // Recuperar todas las convocatorias
            $convocatorias = Convocatoria::all();

            // No se encontraron convocatorias
            if ($convocatorias->isEmpty()) {
                throw new \Exception('No se encontraron convocatorias', 404);
            }

            $convocatorias->each(function ($convocatoria) {
                $convocatoria->documentosConvocatoria->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });
    
            return response()->json(['Convocatorias'=> $convocatorias], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener las convocatorias: ',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    
    public function obtenerConvocatoriaPorId($id)
    {
        try {
            // Buscar la convocatoria por ID
            $convocatoria = Convocatoria::findOrFail($id);

            // Verificar si la convocatoria existe
            if (!$convocatoria) {
                throw new \Exception('Convocatoria no encontrada', 404);
            }
    
    
            // Iterar sobre los documentos y agregar la URL pública si existe el archivo
            $convocatoria->documentosConvocatoria->each(function ($documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            });

            return response()->json(['convocatoria' => $convocatoria], 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener la convocatoria: ',
                'error'   => $e->getMessage()
            ],$e->getCode() ?: 500);
        }
    }

}
