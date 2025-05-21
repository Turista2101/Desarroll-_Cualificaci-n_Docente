<?php
// Declaración del namespace donde se encuentra este controlador, 
// lo que ayuda a organizar el código y evitar conflictos de nombres.

namespace App\Http\Controllers\ApoyoProfesoral;
// Importa el modelo User, que representa a los usuarios en la base de datos.
// Este modelo se usará para consultar y filtrar docentes.

use App\Models\Usuario\User;
// Importa la fachada Log de Laravel, que permite registrar mensajes en los logs del sistema.
// Se utiliza para registrar errores y facilitar la depuración.

use Illuminate\Support\Facades\Log;
// Definición de la clase FiltrarDocentesController, que contiene métodos para filtrar y mostrar información de docentes.
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
             // Consulta todos los usuarios que tengan el rol 'Docente'.
            // whereHas verifica que el usuario tenga al menos un rol con el nombre 'Docente'.
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
             // Eager loading de la relación 'estudiosUsuario' para traer los estudios de cada docente.
            // Esto evita el problema N+1 y mejora el rendimiento.
                ->with('estudiosUsuario') // Sin filtros
            // Ejecuta la consulta y obtiene todos los resultados.
                ->get();
            // Devuelve una respuesta JSON con el estado 'success' y los datos de los usuarios encontrados.
            return response()->json([
                'status' => 'success',
                'data' => $usuarios
            ], 200);
        } catch (\Exception $e) {
            // Si ocurre una excepción, se registra el error en los logs con el mensaje correspondiente.
            Log::error('Error al obtener los estudios: ' . $e->getMessage());
            // Devuelve una respuesta JSON con estado 'error', un mensaje y el error específico.
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
    // Busca un usuario con el rol 'docente' (ojo: aquí está en minúscula, podría causar problemas si los roles son case-sensitive).
            $usuario = User::whereHas('roles', function ($query) {
                $query->where('name', 'docente');
            })
    // Eager loading de la relación 'estudiosUsuario'.
                ->with('estudiosUsuario')
    // Busca el usuario por su ID, lanza excepción si no existe.
                ->findOrFail($id);
    // Devuelve solo los estudios del docente encontrado.
            return response()->json([
                'status' => 'success',
                'data' => $usuario->estudiosUsuario
            ], 200);
        } catch (\Exception $e) {
    // Registra el error en los logs.
            Log::error('Error al obtener estudios del docente: ' . $e->getMessage());
    // Devuelve respuesta de error.
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
            // Eager loading de la relación 'estudiosUsuario', pero solo los estudios que coincidan con el tipo dado.
                ->with(['estudiosUsuario' => function ($query) use ($tipo) {
                    $query->where('tipo_estudio', $tipo);
                }])
                ->get();

            // Solo los que tienen estudios de ese tipo
            $usuariosConEstudios = $usuarios->filter(function ($usuario) {
            // isNotEmpty() verifica que la colección de estudios no esté vacía.
                return $usuario->estudiosUsuario->isNotEmpty();
            })->values();
            // Devuelve los usuarios filtrados.
            return response()->json([
                'status' => 'success',
                'data' => $usuariosConEstudios
            ], 200);
        } catch (\Exception $e) {
            // Registra el error.
            Log::error('Error al filtrar estudios: ' . $e->getMessage());

            // Devuelve respuesta de error.
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
            // Busca todos los usuarios con rol 'Docente' y carga sus idiomas.
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('idiomasUsuario') // Sin filtros
                ->get();
            // Devuelve los usuarios con sus idiomas.
            return response()->json([
                'status' => 'success',
                'data' => $usuarios
            ], 200);
        } catch (\Exception $e) {
            // Registra el error.
            Log::error('Error al obtener los idiomas: ' . $e->getMessage());
            // Devuelve respuesta de error.
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
            // Busca el usuario con rol 'Docente' y carga sus idiomas.
            $usuario = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('idiomasUsuario')
                ->findOrFail($id);
            // Devuelve los idiomas del docente.
            return response()->json([
                'status' => 'success',
                'data' => $usuario->idiomasUsuario
            ], 200);
        } catch (\Exception $e) {
            // Registra el error.
            Log::error('Error al obtener idiomas del docente: ' . $e->getMessage());
            // Devuelve respuesta de error.
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudieron obtener los idiomas del docente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Filtra los docentes por nivel de idioma.
     *
     * @param string $idioma El nivel de idioma a filtrar (por ejemplo, 'Básico', 'Avanzado').
     * @return \Illuminate\Http\JsonResponse
     */
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
            // Devuelve los usuarios filtrados.
            return response()->json([
                'status' => 'success',
                'data' => $usuariosConIdioma
            ], 200);
        } catch (\Exception $e) {
            // Registra el error.
            Log::error('Error al filtrar idiomas: ' . $e->getMessage());
            // Devuelve respuesta de error.
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al filtrar los idiomas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Muestra todas las experiencias de los docentes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
            // Devuelve los usuarios filtrados.
            return response()->json([
                'status' => 'success',
                'data' => $usuariosConExperiencias
            ], 200);
        } catch (\Exception $e) {
            // Registra el error.
            Log::error('Error al obtener experiencias: ' . $e->getMessage());
            // Devuelve respuesta de error.
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener las experiencias.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
/**
     * Filtra los docentes por tipo de experiencia.
     *
     * @param string $tipo El tipo de experiencia a filtrar.
     * @return \Illuminate\Http\JsonResponse
     */
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
            // Devuelve los usuarios filtrados.
            return response()->json([
                'status' => 'success',
                'data' => $usuariosConExperiencias
            ], 200);
        } catch (\Exception $e) {
            // Registra el error.
            Log::error('Error al filtrar experiencias: ' . $e->getMessage());
            // Devuelve respuesta de error.
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al filtrar las experiencias.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Obtiene las experiencias de un docente específico.
     *
     * @param int $id El ID del docente.
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerExperienciasPorDocente($id)
    {
        try {
            // Busca el usuario con rol 'Docente' y carga sus experiencias.
            $usuario = User::whereHas('roles', function ($query) {
                $query->where('name', 'Docente');
            })
                ->with('experienciasUsuario')
                ->findOrFail($id);
            // Devuelve las experiencias del docente.
            return response()->json([
                'status' => 'success',
                'data' => $usuario->experienciasUsuario
            ], 200);
        } catch (\Exception $e) {
            // Registra el error.
            Log::error('Error al obtener experiencias del docente: ' . $e->getMessage());
            // Devuelve respuesta de error.
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudieron obtener las experiencias del docente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
/**
     * Muestra toda la producción académica de los docentes.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function mostrarTodaLaProduccionAcademica()
    {
        try {
            // Busca usuarios con rol 'Docente' y carga toda su producción académica.
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
