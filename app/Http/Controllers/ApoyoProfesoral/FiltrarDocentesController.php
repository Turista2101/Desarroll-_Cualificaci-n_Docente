<?php

namespace App\Http\Controllers\ApoyoProfesoral;

use App\Models\Usuario\User;

class FiltrarDocentesController
{
    public function obtenerDocentesPorTipoEstudio($tipo)
    {
        try {
            $docentes = User::role('Docente')
                ->with('estudiosUsuario')
                ->get()
                ->filter(function ($docente) use ($tipo) {
                    return $docente->estudiosUsuario->contains(function ($estudio) use ($tipo) {
                        return $estudio->tipo_estudio === $tipo;
                    });
                })
                ->map(function ($docente) use ($tipo) {
                    // Solo dejar los estudios con el tipo exacto
                    $docente->estudiosUsuario = $docente->estudiosUsuario
                        ->filter(fn($estudio) => $estudio->tipo_estudio === $tipo)
                        ->values();

                    // Oculta cualquier atributo duplicado
                    $docente->makeHidden(['estudios_usuario']); // ocultar si existe por duplicado

                    return $docente;
                })
                ->values();

            return response()->json([
                'data' => $docentes,
                'total' => $docentes->count(),
                'message' => $docentes->isEmpty()
                    ? "No se encontraron docentes con tipo de estudio: $tipo"
                    : null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener docentes por tipo de estudio.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
