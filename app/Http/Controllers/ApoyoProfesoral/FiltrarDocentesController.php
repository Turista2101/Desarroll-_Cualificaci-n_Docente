<?php

namespace App\Http\Controllers\ApoyoProfesoral;

use App\Models\Usuario\User;
use Illuminate\Support\Facades\Log;

class FiltrarDocentesController
{
    /**
     * Muestra todos los estudios de los docentes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mostrarTodosLosEstudios()
    {
        try {
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('estudiosUsuario') // Sin filtros
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $usuarios
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener los estudios: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener los estudios.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Muestra los estudios de un docente específico.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerEstudiosPorDocente($id)
    {
        try {
            $usuario = User::whereHas('roles', function ($query) {
                $query->where('name', 'docente');
            })
                ->with('estudiosUsuario')
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $usuario->estudiosUsuario
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener estudios del docente: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudieron obtener los estudios del docente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Filtra los docentes por tipo de estudio.
     *
     * @param string $tipo
     * @return \Illuminate\Http\JsonResponse
     */
    public function filtrarPorTipoEstudio($tipo)
    {
        try {
            // Filtrar usuarios con rol docente
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with(['estudiosUsuario' => function ($query) use ($tipo) {
                    $query->where('tipo_estudio', $tipo);
                }])
                ->get();

            // Solo los que tienen estudios de ese tipo
            $usuariosConEstudios = $usuarios->filter(function ($usuario) {
                return $usuario->estudiosUsuario->isNotEmpty();
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $usuariosConEstudios
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al filtrar estudios: ' . $e->getMessage());


            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al filtrar los estudios.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Muestra todos los idiomas de los docentes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mostrarTodosLosIdiomas()
    {
        try {
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('idiomasUsuario') // Sin filtros
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $usuarios
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener los idiomas: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener los idiomas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filtra los docentes por idioma.
     *
     * @param string $idioma
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerIdiomasPorDocente($id)
    {
        try {
            $usuario = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('idiomasUsuario')
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $usuario->idiomasUsuario
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener idiomas del docente: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudieron obtener los idiomas del docente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function filtrarPorNivelIdioma($idioma)
    {
        try {
            // Filtrar usuarios con rol docente
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with(['idiomasUsuario' => function ($query) use ($idioma) {
                    $query->where('nivel', $idioma);
                }])
                ->get();

            // Solo los que tienen ese idioma
            $usuariosConIdioma = $usuarios->filter(function ($usuario) {
                return $usuario->idiomasUsuario->isNotEmpty();
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $usuariosConIdioma
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al filtrar idiomas: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al filtrar los idiomas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerTodasLasExperiencias()
    {
        try {
            // Filtrar usuarios con rol Docente y traer todas sus experiencias
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('experienciasUsuario')
                ->get();

            // Solo los que tienen experiencias
            $usuariosConExperiencias = $usuarios->filter(function ($usuario) {
                return $usuario->experienciasUsuario->isNotEmpty();
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $usuariosConExperiencias
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener experiencias: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener las experiencias.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function filtrarPorTipoExperiencia($tipo)
    {
        try {
            // Filtrar usuarios con rol docente
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with(['experienciasUsuario' => function ($query) use ($tipo) {
                    $query->where('tipo_experiencia', $tipo);
                }])
                ->get();

            // Solo los que tienen experiencias del tipo indicado
            $usuariosConExperiencias = $usuarios->filter(function ($usuario) {
                return $usuario->experienciasUsuario->isNotEmpty();
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $usuariosConExperiencias
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al filtrar experiencias: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al filtrar las experiencias.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function obtenerExperienciasPorDocente($id)
    {
        try {
            $usuario = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('experienciasUsuario')
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $usuario->experienciasUsuario
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener experiencias del docente: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudieron obtener las experiencias del docente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function mostrarTodaLaProduccionAcademica()
    {
        try {
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('produccionAcademicaUsuario') // Sin filtros
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $usuarios
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener la producción académica: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener la producción académica.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerProduccionAcademicaPorDocente($id)
    {
        try {
            $usuario = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('produccionAcademicaUsuario')
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $usuario->produccionAcademicaUsuario
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener la producción académica del docente: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo obtener la producción académica del docente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
public function filtrarPorAmbitoDivulgacion($ambitoId)
{
    try {
        $usuarios = User::whereHas('roles', function ($query) {
            $query->where('name', 'Docente');
        })
        ->with(['produccionAcademicaUsuario' => function ($query) use ($ambitoId) {
            $query->where('ambito_divulgacion_id', $ambitoId);
        }])
        ->get();

        $usuariosConProduccion = $usuarios->filter(function ($usuario) {
            return $usuario->produccionAcademicaUsuario->isNotEmpty();
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $usuariosConProduccion
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error al filtrar la producción académica por ámbito: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Ocurrió un error al filtrar la producción académica.',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
