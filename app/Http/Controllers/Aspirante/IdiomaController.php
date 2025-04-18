<?php

namespace App\Http\Controllers\Aspirante;

use App\Constants\ConstAgregarIdioma\NivelIdioma;
use App\Http\Requests\RequestAspirante\RequestIdioma\ActualizarIdiomaRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Idioma;
use App\Models\Aspirante\Documento; // Importar el modelo Documento
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RequestAspirante\RequestIdioma\CrearIdiomaRequest; // Importar la clase de solicitud personalizada
use Illuminate\Support\Facades\DB; // Importar la clase DB para transacciones



class IdiomaController
{
    // Guardar un nuevo idioma en la base de datos
    public function crearIdioma(CrearIdiomaRequest $request)
    {
        try{
            $idioma = DB::transaction(function ()use($request) {
                // Validar los datos de entrada
                $datosIdioma = $request->validated();

                //Agregar el user_id del usuario autenticado
                $datosIdioma['user_id'] = $request->user()->id;

                // Crear un nuevo idioma
                $idioma = Idioma::create($datosIdioma);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Idiomas', $nombreArchivo, 'public');


                // Guardar el documento relacionado con el idioma
                    Documento::create([

                        'archivo'        => str_replace('public/','', $rutaArchivo),
                        'estado'         => 'pendiente',
                        'documentable_id' => $idioma->id_idioma, // Relación polimórfica
                        'documentable_type' => Idioma::class,
                    ]);
                 }

                return $idioma;
          });

            return response()->json([
                'mensaje'  => 'Idioma y documento guardados correctamente',
                'idioma'   => $idioma,
            ], 201);
    
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el idioma',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Obtener todos los registros de idiomas del usuario autenticado
    public function obtenerIdiomas(Request $request)
    {
        try{
             $user = $request->user();

            // Verificar si el usuario está autenticado
             if (!$user) {
                throw new \Exception( 'Usuario no autenticado', 401);
            }

            // Obtener todos los idiomas relacionados con el usuario autenticado
            $idiomas = Idioma::where('user_id', $user->id)
            ->with(['documentosIdioma' => function ($query) {
                $query->select('id_documento', 'documentable_id', 'archivo', 'estado'); // Relación polimórfica usa documentable_id
            }])
            ->orderby('created_at')
            ->get();
            // Verificar si se encontraron idiomas
            if ($idiomas->isEmpty()) {
                throw new \Exception('No se encontraron idiomas', 404);
            }

            // Agregar el Url a cada documento si existe
            $idiomas->each(function ($idioma) {
                $idioma->documentosIdioma->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });
            });

            return response()->json(['idiomas' => $idiomas], 200);

        }catch(\Exception $e){
            return response()->json([
            'message' => 'Error al obtener los idiomas',
            'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }


    public function obtenerIdiomaPorId(Request $request, $id)
    {
        try{
             $user = $request->user();

            // Verificar si el usuario está autenticado
             if (!$user) {
                throw new \Exception( 'Usuario no autenticado', 401);
            }

            // Obtener todos los idiomas relacionados con el usuario autenticado
            $idioma = Idioma::where('id_idioma', $id)
                ->where('user_id', $user->id)
                ->with(['documentosIdioma' => function ($query) {
                    $query->select('id_documento', 'documentable_id', 'archivo', 'estado'); // Relación polimórfica usa documentable_id
                }])
                ->first();

            // Verificar si se encontraron idiomas
            if (!$idioma) {
                throw new \Exception('No se encontraron idiomas', 404);
            }

            // Agregar el Url a cada documento si existe
                $idioma->documentosIdioma->each(function ($documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                });

            return response()->json(['idiomas' => $idioma], 200);

        }catch(\Exception $e){
            return response()->json([
            'message' => 'Error al obtener los idiomas',
            'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }


    // Actualizar un registro de idioma
    public function actualizarIdioma(ActualizarIdiomaRequest $request, $id)
    {
        try{
            $idioma =DB::transaction(function () use($request, $id) {
                $user = $request->user();
                
                // Buscar el estudio que tenga documentos del usuario autenticado
                $idioma = Idioma::where('id_idioma', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

                $datosIdiomaActualizar =$request->validated();
                $idioma->update($datosIdiomaActualizar);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Idiomas', $nombreArchivo, 'public');

                    // Buscar el documento asociado
                    $documento = Documento::where('documentable_id', $idioma->id_idioma)
                        ->where('documentable_type', Idioma::class)
                        ->first();

                    if ($documento) {
                        Storage::disk('public')->delete($documento->archivo);
                        $documento->update([
                            'archivo' => str_replace('public/','', $rutaArchivo),
                            'estado'  => 'pendiente',
                        ]);
                    } else {
                    // Crear un nuevo documento si no existe
                        Documento::create([
                            'user_id'        => $request->user()->id,
                            'archivo'        => str_replace('public/','', $rutaArchivo),
                            'estado'         => 'pendiente',
                            'documentable_id' => $idioma->id_idioma,
                            'documentable_type' => Idioma::class,
                        ]);
                    }
                }
                return $idioma;
            });

            return response()->json([
                'mensaje'  => 'Idioma actualizado correctamente',
                'data'   => $idioma->refresh(),
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error al actualizar el idioma',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Eliminar un registro de idioma
    public function eliminarIdioma(Request $request, $id)
    {
        try{

            $user = $request->user();
            // Buscar el idioma que tenga documentos del usuario autenticado
            $idioma = Idioma::where('id_idioma', $id)
            ->where('user_id', $user->id)
            ->with('documentosIdioma')
            ->first();

            if (!$idioma) {
                return response()->json(['error' => 'Idioma no encontrado'], 404);
            }
            DB::transaction(function () use($idioma) {
                foreach ($idioma->documentosIdioma as $documento) {
                    // Eliminar el archivo del almacenamiento si existe
                    if (!empty($documento->archivo) && Storage::disk('public')->exists($documento->archivo)) {
                        Storage::disk('public')->delete ($documento->archivo);
                    }
                    $documento->delete(); // Eliminar el documento de la base de datos
                }
                // Eliminar el idioma
                $idioma->delete();
                
            });
 
            return response()->json(['mensaje' => 'Idioma eliminado correctamente'], 200);
        
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error al eliminar el idioma',
                'error' => $e->getMessage()
            ], 500);
         }
}
}
