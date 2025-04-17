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
/**
 * @OA\Post(
 *     path="/aspirante/informacion-contacto",
 *     tags={"Información de Contacto"},
 *     summary="Crear información de contacto",
 *     description="Crea un nuevo registro de información de contacto con archivo adjunto. Requiere autenticación.",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"telefono", "direccion", "ciudad", "archivo"},
 *                 @OA\Property(property="telefono", type="string", example="3123456789"),
 *                 @OA\Property(property="direccion", type="string", example="Calle 123 #45-67"),
 *                 @OA\Property(property="ciudad", type="string", example="Popayán"),
 *                 @OA\Property(property="archivo", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Información de contacto creada",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Información de contacto y documento guardados correctamente"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(response=500, description="Error interno")
 * )
 */


    //Crear un registro de información de contacto
    public function crearInformacionContacto(CrearInformacionContactoRequest $request)
    {
        try {
            $informacionContacto= DB::transaction(function () use ($request) {
                // Validar los datos de la solicitud
                $datosInfomacionContacto = $request->validated();

                // Crear información de contacto
                $informacionContacto = InformacionContacto::create($datosInfomacionContacto);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Indentificacion', $nombreArchivo, 'public');

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



/**
 * @OA\Get(
 *     path="/aspirante/informacion-contacto",
 *     tags={"Información de Contacto"},
 *     summary="Obtener información de contacto",
 *     description="Retorna la información de contacto del usuario autenticado. Requiere autenticación.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Información de contacto obtenida",
 *         @OA\JsonContent(
 *             @OA\Property(property="informacion_contacto", type="object")
 *         )
 *     ),
 *     @OA\Response(response=404, description="Información no encontrada"),
 *     @OA\Response(response=401, description="No autenticado")
 * )
 */


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
            $informacionContacto = InformacionContacto::whereHas('documentosInformacionContacto', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['documentosInformacionContacto' => function ($query) {
                $query->select('id_documento', 'documentable_id', 'archivo', 'user_id', );
            }])->first();

            //verificar si el usuario tiene información de contacto
            if (!$informacionContacto) {
                throw new \Exception('No se encontró información de contacto', 404);
            }

            //verificar si la información de contacto existe
            if (!$informacionContacto) {
                return response()->json(['message' => 'No se encontró información de contacto'], 404);
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


/**
 * @OA\Put(
 *     path="/aspirante/informacion-contacto",
 *     tags={"Información de Contacto"},
 *     summary="Actualizar información de contacto",
 *     description="Actualiza los datos de información de contacto del usuario autenticado. Requiere autenticación.",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="telefono", type="string", example="3201234567"),
 *                 @OA\Property(property="direccion", type="string", example="Carrera 12 #34-56"),
 *                 @OA\Property(property="ciudad", type="string", example="Cali"),
 *                 @OA\Property(property="archivo", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Información de contacto actualizada correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Información de contacto actualizada correctamente"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(response=500, description="Error al actualizar")
 * )
 */

    //Actualizar información de contacto
    public function actualizarInformacionContacto(ActualizarInformacionContactoRequest $request)
    {
        try{
            $informacionContacto= DB::transaction(function () use ($request) {
                // Obtener el usuario autenticado
                $user = $request->user();

                // Buscar el registro de información de contacto por ID
                $informacionContacto = InformacionContacto::whereHas('documentosInformacionContacto', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->firstOrFail();

                $datosInfomacionContactoActualizar = $request->validated();

                // Actualizar solo los campos que se envían en la solicitud
                $informacionContacto->update($datosInfomacionContactoActualizar);

                // Verificar si se envió un archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Indentificacion', $nombreArchivo, 'public');

                    // Buscar el documento asociado
                    $documento = Documento::where('documentable_id', $informacionContacto->id_informacion_contacto)
                        ->where('documentable_type', InformacionContacto::class)
                        ->where('user_id', $user->id)
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
                            'user_id'          => $user->id,
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
                    'data'    => $informacionContacto
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la información de contacto o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
