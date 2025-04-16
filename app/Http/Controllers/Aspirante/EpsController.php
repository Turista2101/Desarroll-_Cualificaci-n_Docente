<?php
namespace App\Http\Controllers\Aspirante;

use App\Http\Requests\RequestAspirante\RequestEps\ActualizarEpsRequest;
use Illuminate\Http\Request;
use App\Models\Aspirante\Eps;
use App\Models\Aspirante\Documento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RequestAspirante\RequestEps\CrearEpsRequest;

/**
 * @OA\Info(
 *     title="API de Gestión de EPS",
 *     version="1.0.0",
 *     description="Endpoints para manejar información de EPS y documentos asociados."
 * )
 * @OA\Server(url="http://localhost:8000/api")
 */

class EpsController
{
    /**
     * Crear un registro de EPS con documento adjunto.
     *
     * @OA\Post(
     *     path="/aspirante/crear-eps",
     *     tags={"EPS"},
     *     summary="Crear EPS",
     *     description="Crea una nueva EPS y sube un archivo asociado (PDF, JPG, PNG). Requiere autenticación.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nombre_eps", "tipo_afiliacion", "estado_afiliacion", "fecha_afiliacion_efectiva", "tipo_afiliado", "archivo"},
     *                 @OA\Property(property="nombre_eps", type="string", minLength=7, maxLength=100, example="Salud Total EPS"),
     *                 @OA\Property(property="tipo_afiliacion", type="string", enum={"contributivo", "subsidiado", "especial"}, example="contributivo"),
     *                 @OA\Property(property="estado_afiliacion", type="string", enum={"activo", "inactivo", "pendiente"}, example="activo"),
     *                 @OA\Property(property="fecha_afiliacion_efectiva", type="string", format="date", example="2023-01-15"),
     *                 @OA\Property(property="fecha_finalizacion_afiliacion", type="string", format="date", nullable=true, example="2025-01-15"),
     *                 @OA\Property(property="tipo_afiliado", type="string", enum={"titular", "beneficiario"}, example="titular"),
     *                 @OA\Property(property="numero_afiliado", type="string", maxLength=100, nullable=true, example="AF123456"),
     *                 @OA\Property(property="archivo", type="string", format="binary", description="Archivo PDF, JPG o PNG (máx. 2MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="EPS creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="EPS y documento creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/EPS")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al crear la EPS"),
     *             @OA\Property(property="error", type="string", example="Detalles del error...")
     *         )
     *     )
     * )
     */

    //Crear un registro de eps
    public function crearEps(CrearEpsRequest $request)
    {
        try {
            $eps = DB::transaction(function () use ($request) {
                // Validar los datos de la solicitud
                $datosEpsCrear = $request->validated();
    
                // Crear EPS
                $eps = Eps::create($datosEpsCrear);
    
                // Subir archivo si existe
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Eps', $nombreArchivo, 'public');
    
                    Documento::create([
                        'user_id'           => $request->user()->id,
                        'archivo'           => str_replace('public/', '', $rutaArchivo),
                        'estado'            => 'pendiente',
                        'documentable_id'   => $eps->id_eps,
                        'documentable_type' => Eps::class,
                    ]);
                }
    
                return $eps;
            });
    
            return response()->json([
                'message' => 'EPS y documento creado exitosamente',
                'data'    => $eps
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la EPS o subir el archivo.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Schema(
     *     schema="EPS",
     *     type="object",
     *     @OA\Property(property="id_eps", type="integer", example=1),
     *     @OA\Property(property="nombre_eps", type="string", example="Salud Total EPS"),
     *     @OA\Property(property="tipo_afiliacion", type="string", example="contributivo"),
     *     @OA\Property(property="estado_afiliacion", type="string", example="activo"),
     *     @OA\Property(property="fecha_afiliacion_efectiva", type="string", format="date", example="2023-01-15"),
     *     @OA\Property(property="fecha_finalizacion_afiliacion", type="string", format="date", nullable=true),
     *     @OA\Property(property="tipo_afiliado", type="string", example="titular"),
     *     @OA\Property(property="numero_afiliado", type="string", nullable=true)
     * )
     * @OA\Schema(
     *     schema="Documento",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="archivo", type="string", example="documentos/Eps/123456789_carnet.pdf"),
     *     @OA\Property(property="estado", type="string", example="pendiente")
     * )
     */
    


    //Obtener la información de eps del usuario autenticado
    public function obtenerEps(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
    
            // verificar si el usuario esta autenticado
            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }
    
            //obtener solo los estudios que tiene documentos pertenecientes al usuario autenticado
            $eps = Eps::whereHas('documentosEps', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['documentosEps' => function ($query) {
                $query->select('id_documento', 'documentable_id', 'archivo', 'user_id');
            }])->first();
    
            //verificar si el eps existe
            if (!$eps) {
                throw new \Exception('No se encontró información de EPS', 404);
            }
    
            //Agregar la URL del archivo a cada documento si existe
            foreach ($eps->documentosEps as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }
    
            return response()->json(['eps' => $eps], 200);
    
        } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error al actualizar el EPS',
                    'error'   => $e->getMessage()
            ],$e->getCode() ?: 500);
        }
    }



    //actualizar eps
    public function actualizarEps(ActualizarEpsRequest $request)
    {
        try {
            $eps = DB::transaction(function () use ($request) {
                $user = $request->user();
    
                // Buscar el EPS del usuario autenticado
                $eps = Eps::whereHas('documentosEps', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->firstOrFail();
    
                // Validar y actualizar los campos
                $datosEpsActualizar = $request->validated();
                $eps->update($datosEpsActualizar);
    
                // Manejo del archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Eps', $nombreArchivo, 'public');
    
                    // Buscar documento existente
                    $documento = Documento::where('documentable_id', $eps->id_eps)
                        ->where('documentable_type', Eps::class)
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
                            'user_id'           => $user->id,
                            'archivo'           => str_replace('public/', '', $rutaArchivo),
                            'estado'            => 'pendiente',
                            'documentable_id'   => $eps->id_eps,
                            'documentable_type' => Eps::class,
                        ]);
                    }
                }
    
                return $eps;
            });
    
            return response()->json([
                'message' => 'EPS actualizado exitosamente',
                'data'    => $eps->fresh()
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el EPS',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



}
