<?php

namespace App\Http\Controllers\EvaluadorProduccion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Usuario\User;
use App\Models\Aspirante\Documento;

class EvaluadorProduccionController
{
    // Ver todas las producciones académicas pendientes de todos los usuarios
    public function obtenerProducciones()
    {
        try {
            $usuarios = User::with(['produccionAcademicaUsuario.documentosProduccionAcademica' => function ($query) {
                $query->where('estado', 'pendiente');
            }])
                ->whereHas('produccionAcademicaUsuario.documentosProduccionAcademica', function ($query) {
                    $query->where('estado', 'pendiente');
                })
                ->get();

            // Añadir URL del archivo a cada documento
            foreach ($usuarios as $usuario) {
                foreach ($usuario->produccionAcademicaUsuario as $produccion) {
                    foreach ($produccion->documentosProduccionAcademica as $documento) {
                        $documento->archivo_url = Storage::url($documento->archivo);
                    }
                }
            }

            return response()->json([
                'data' => $usuarios,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las producciones académicas pendientes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Ver las producciones académicas pendientes de un usuario específico
    public function verProduccionesPorUsuario($user_id)
    {
        try {
            $user = User::with(['produccionAcademicaUsuario.documentosProduccionAcademica' => function ($query) {
                $query->where('estado', 'pendiente');
            }])->findOrFail($user_id);

            // Añadir URL del archivo a cada documento
            foreach ($user->produccionAcademicaUsuario as $produccion) {
                foreach ($produccion->documentosProduccionAcademica as $documento) {
                    $documento->archivo_url = Storage::url($documento->archivo);
                }
            }

            return response()->json([
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las producciones académicas del usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function actualizarEstadoDocumento(Request $request, $documento_id)
    {
        try {
            // Validar estado
            $request->validate([
                'estado' => 'required|in:aprobado,rechazado',
            ]);

            // Buscar el documento
            $documento = Documento::findOrFail($documento_id);

            // Asegurarse de que pertenece a una Producción Académica
            if (!str_contains($documento->documentable_type, 'ProduccionAcademica')) {
                return response()->json([
                    'message' => 'Este documento no pertenece a una producción académica.'
                ], 403);
            }

            // Actualizar estado
            $documento->estado = $request->estado;
            $documento->save();

            return response()->json([
                'message' => "El documento fue marcado como {$documento->estado} exitosamente.",
                'data' => $documento,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el estado del documento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
