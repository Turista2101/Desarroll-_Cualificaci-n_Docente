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
    /**
     * Crear un registro de RUT con documento adjunto.
     *
     * @OA\Post(
     *     path="/aspirante/crear-rut",
     *     tags={"RUT"},
     *     summary="Crear RUT",
     *     description="Crea un nuevo RUT y sube un archivo asociado (PDF, JPG, PNG). Requiere autenticación.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"numero_rut", "razon_social", "tipo_persona", "codigo_ciiu", "responsabilidades_tributarias", "archivo"},
     *                 @OA\Property(property="numero_rut", type="string", minLength=7, maxLength=100, example="123456789"),
     *                 @OA\Property(property="razon_social", type="string", minLength=7, maxLength=100, example="Empresa XYZ S.A."),
     *                 @OA\Property(property="tipo_persona", type="string", enum={"Natural", "Juridico"}, example="Juridico"),
     *                 @OA\Property(property="codigo_ciiu", type="string", enum={
        *                         "Agricultura, ganadería, caza, silvicultura y pesca",
        *                         "Explotación de minas y canteras",
        *                         "Industria manufacturera",
        *                         "Suministro de electricidad, gas, vapor y aire acondicionado",
        *                         "Suministro de agua, alcantarillado, gestión de residuos y actividades de saneamiento",
        *                         "Construcción",
        *                         "Comercio al por mayor y al por menor; reparación de vehículos automotores y motocicletas",
        *                         "Transporte y almacenamiento",
        *                         "Alojamiento y servicios de comida",
        *                         "Información y comunicaciones",
        *                         "Actividades financieras y de seguros",
        *                         "Actividades inmobiliarias",
        *                         "Actividades profesionales, científicas y técnicas",
        *                         "Actividades administrativas y de servicios auxiliares",
        *                         "Administración pública y defensa; seguridad social obligatoria",
        *                         "Educación",
        *                         "Actividades de salud humana y de asistencia social",
        *                         "Artes, entretenimiento y recreación",
        *                         "Otras actividades de servicios",
        *                         "Actividades de los hogares como empleadores; actividades de los hogares como productores de bienes y servicios para uso propio",
        *                         "Organizaciones y organismos extraterritoriales"
        *                          }, 
        *                          example="Industria manufacturera"
        *                    ),    
     *                 @OA\Property(property="responsabilidades_tributarias", type="string", minLength=7, maxLength=100, example="IVA, Renta"),
     *                 @OA\Property(property="archivo", type="string", format="binary", description="Archivo PDF, JPG o PNG (máx. 2MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="RUT creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RUT creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Rut")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error en el formulario"),
     *             @OA\Property(property="errors", type="object", additionalProperties={"type": "string"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al crear el RUT"),
     *             @OA\Property(property="error", type="string", example="Detalles del error...")
     *         )
     *     )
     * )
     */
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

    /**
     * Obtener información del RUT del usuario autenticado.
     *
     * @OA\Get(
     *     path="/aspirante/obtener-rut",
     *     tags={"RUT"},
     *     summary="Obtener RUT",
     *     description="Obtiene la información del RUT y los documentos asociados del usuario autenticado.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Información del RUT obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="ruts", type="object",
     *                 @OA\Property(property="id_rut", type="integer", example=1),
     *                 @OA\Property(property="numero_rut", type="string", example="123456789"),
     *                 @OA\Property(property="razon_social", type="string", example="Empresa XYZ S.A."),
     *                 @OA\Property(property="tipo_persona", type="string", example="Juridico"),
     *                 @OA\Property(property="codigo_ciiu", type="string", example="C2023"),
     *                 @OA\Property(property="responsabilidades_tributarias", type="string", example="IVA, Renta"),
     *                 @OA\Property(property="documentosRut", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id_documento", type="integer", example=1),
     *                         @OA\Property(property="archivo", type="string", example="documentos/Rut/archivo.pdf"),
     *                         @OA\Property(property="archivo_url", type="string", example="http://localhost/storage/documentos/Rut/archivo.pdf"),
     *                         @OA\Property(property="user_id", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontró información de RUT",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontró información de RUT")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al obtener la información de RUT"),
     *             @OA\Property(property="error", type="string", example="Detalles del error...")
     *         )
     *     )
     * )
     */
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

    //en este caso se mantiene el Post y en frontend emularían la funcion del PUT con _method
    //según mi mejor amiga, no se puede emular directamente aquí

    /**
     * Actualizar un registro de RUT del usuario autenticado.
     *
     * @OA\Post(
     *     path="/aspirante/actualizar-rut",
     *     tags={"RUT"},
     *     summary="Actualizar RUT",
     *     description="Actualiza un registro de RUT existente y permite reemplazar el archivo asociado (PDF, JPG, PNG). Requiere autenticación.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"numero_rut", "razon_social", "tipo_persona", "codigo_ciiu", "responsabilidades_tributarias"},
     *                 @OA\Property(property="numero_rut", type="string", minLength=7, maxLength=100, example="123456789"),
     *                 @OA\Property(property="razon_social", type="string", minLength=7, maxLength=100, example="Empresa XYZ S.A."),
     *                 @OA\Property(property="tipo_persona", type="string", enum={"Natural", "Juridico"}, example="Juridico"),
     *                 @OA\Property(
     *                     property="codigo_ciiu",
     *                     type="string",
     *                     enum={
     *                         "Agricultura, ganadería, caza, silvicultura y pesca",
     *                         "Explotación de minas y canteras",
     *                         "Industria manufacturera",
     *                         "Suministro de electricidad, gas, vapor y aire acondicionado",
     *                         "Suministro de agua, alcantarillado, gestión de residuos y actividades de saneamiento",
     *                         "Construcción",
     *                         "Comercio al por mayor y al por menor; reparación de vehículos automotores y motocicletas",
     *                         "Transporte y almacenamiento",
     *                         "Alojamiento y servicios de comida",
     *                         "Información y comunicaciones",
     *                         "Actividades financieras y de seguros",
     *                         "Actividades inmobiliarias",
     *                         "Actividades profesionales, científicas y técnicas",
     *                         "Actividades administrativas y de servicios auxiliares",
     *                         "Administración pública y defensa; seguridad social obligatoria",
     *                         "Educación",
     *                         "Actividades de salud humana y de asistencia social",
     *                         "Artes, entretenimiento y recreación",
     *                         "Otras actividades de servicios",
     *                         "Actividades de los hogares como empleadores; actividades de los hogares como productores de bienes y servicios para uso propio",
     *                         "Organizaciones y organismos extraterritoriales"
     *                     },
     *                     example="Industria manufacturera"
     *                 ),
     *                 @OA\Property(property="responsabilidades_tributarias", type="string", minLength=7, maxLength=100, example="IVA, Renta"),
     *                 @OA\Property(property="archivo", type="string", format="binary", description="Archivo PDF, JPG o PNG (máx. 2MB)", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="RUT actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rut actualizado correctamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Rut")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontró el RUT",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontró el RUT")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al actualizar el RUT"),
     *             @OA\Property(property="error", type="string", example="Detalles del error...")
     *         )
     *     )
     * )
     */
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
