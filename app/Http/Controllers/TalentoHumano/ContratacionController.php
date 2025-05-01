<?php

namespace App\Http\Controllers\TalentoHumano;

use App\Models\Aspirante\Documento;
use App\Http\Requests\RequestTalentoHumano\RequestContratacion\CrearContratacionRequest;
use App\Http\Requests\RequestTalentoHumano\RequestContratacion\ActualizarContratacionRequest;
use App\Models\Usuario\User;
use Illuminate\Support\Facades\DB;
use App\Models\TalentoHumano\Contratacion; // Importar la clase Contratacion
use Illuminate\Support\Facades\Auth;
use App\Services\AprobarDocumentosService; // Importar el servicio AprobarDocumentosService
use App\Services\RevertirDocumentosService;

class ContratacionController
{
    protected $aprobarDocumentosService;
    protected $revertirDocumentosService;
   

    public function __construct(AprobarDocumentosService $aprobarDocumentosService,RevertirDocumentosService $revertirDocumentosService)
    {
        $this->aprobarDocumentosService = $aprobarDocumentosService;
        $this->revertirDocumentosService = $revertirDocumentosService;
    }

    public function crearContratacion(CrearContratacionRequest $request, $user_id)
    {
        try {
            $contratacion = DB::transaction(function () use ($request, $user_id) {
                $datosContratacion = $request->validated();
                $datosContratacion['user_id'] = $user_id; // Asignar el user_id a los datos de contratación

                // Verificar si ya existe una contratación para el usuario
                $existeContratacion = Contratacion::where('user_id', $user_id)->exists();
                if ($existeContratacion) {
                    throw new \Exception('El usuario ya tiene una contratación existente.', 409);
                }

                // Verificamos que el usuario exista
                $usuario = User::findOrFail($user_id);

                // Crear la contratación
                $contratacion = Contratacion::create($datosContratacion);

                // Cambiar el rol a 'docente'
                $usuario->syncRoles(['Docente']);

                // Aprobar documentos del usuario
                $this->aprobarDocumentosService->aprobarDocumentosDeUsuario($usuario);

                return $contratacion;
            });

            return response()->json([
                'message' => 'Contratación creada y rol actualizado a docente.',
                'contratacion' => $contratacion
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error',
                'error' => $e->getMessage()
            ], is_numeric($e->getCode()) ? (int) $e->getCode() : 500);
        }
    }

    public function actualizarContratacion(ActualizarContratacionRequest $request, $id_contratacion)
    {
        try {
            $contratacion = DB::transaction(function () use ($request, $id_contratacion) {
                $contratacion = Contratacion::findOrFail($id_contratacion);

                $datosActualizarContratacion = $request->validated();

                // Actualizar la contratación
                $contratacion->update($datosActualizarContratacion);

                return $contratacion;
            });

            return response()->json([
                'message' => 'Contratación actualizada correctamente.',
                'contratacion' => $contratacion
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la contratación.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function eliminarContratacion($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $contratacion = Contratacion::findOrFail($id);
                $usuario = $contratacion->UsuarioContratacion;

                // Eliminar contratación
                $contratacion->delete();

                // Cambiar el rol a 'aspirante'
                if ($usuario) {
                    $usuario->syncRoles(['Aspirante']);

                    // Revertir documentos del usuario
                    $this->revertirDocumentosService->revertirDocumentosDeUsuario($usuario);
                }
            });

            return response()->json([
                'message' => 'Contratación eliminada y rol cambiado a aspirante.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la contratación.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function obtenerContratacionPorId($id_contratacion)
    {
        try {
            // Buscar la contratación con la relación al usuario
            $contratacion = Contratacion::with('UsuarioContratacion') // Si tienes relación con el modelo User
                ->findOrFail($id_contratacion);

            return response()->json([
                'message' => 'Información de contratación obtenida correctamente.',
                'contratacion' => $contratacion
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la información de la contratación.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function obtenerTodasLasContrataciones()
    {
        try {
            $contrataciones = Contratacion::with('UsuarioContratacion') // Asegúrate de tener la relación 'usuario' definida en el modelo Contratacion
                ->orderBy('fecha_inicio', 'desc')
                ->get();

            return response()->json([
                'message' => 'Contrataciones obtenidas correctamente.',
                'contrataciones' => $contrataciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las contrataciones.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // public function obtenerContratacionesPorUsuario($user_id)
    // {
    //     try {
    //         $contrataciones = Contratacion::where('user_id', $user_id)
    //             ->orderBy('fecha_inicio', 'desc')
    //             ->get();

    //         if ($contrataciones->isEmpty()) {
    //             return response()->json([
    //                 'message' => 'No se encontraron contrataciones para este usuario.'
    //             ], 404);
    //         }

    //         return response()->json([
    //             'message' => 'Contrataciones obtenidas correctamente.',
    //             'contrataciones' => $contrataciones
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Error al obtener las contrataciones del usuario.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function obtenerContratacionUsuario()
    {
        try {
            $usuario = Auth::user(); // También puedes usar auth()->user();

            if (!$usuario) {
                throw new  \Exception('No hay usuario autenticado.', 401);
            }

            $contrataciones = Contratacion::where('user_id', $usuario->id)
                ->orderBy('fecha_inicio', 'desc')
                ->get();

            if ($contrataciones->isEmpty()) {
                throw new  \Exception('No se encontraron contrataciones para el usuario autenticado.', 404);
            }

            return response()->json([
                'message' => 'Contrataciones del usuario autenticado obtenidas correctamente.',
                'contrataciones' => $contrataciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las contrataciones del usuario autenticado.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
