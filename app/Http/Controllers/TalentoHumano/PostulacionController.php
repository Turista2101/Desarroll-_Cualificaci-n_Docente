<?php

namespace App\Http\Controllers\TalentoHumano;

use App\Constants\ConstTalentoHumano\EstadoPostulacion;
use App\Models\TalentoHumano\Postulacion;
use App\Models\Usuario\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NotificacionGeneral;

class PostulacionController
{
    public function crearPostulacion(Request $request, $convocatoriaId)
    {
        try {
            $postulacion = DB::transaction(function () use ($request, $convocatoriaId) {
                $user = $request->user();

                // Verificar si ya se ha postulado
                $existe = Postulacion::where('user_id', $user->id)
                    ->where('convocatoria_id', $convocatoriaId)
                    ->exists();

                if ($existe) {
                    throw new \Exception('Ya te has postulado a esta convocatoria', 409);
                }
                
                $user->notify(new NotificacionGeneral("Te has postulado a una nueva convocatoria."));

                $talentoHumano = User::role('Talento Humano')->get();
                Notification::send($talentoHumano, new NotificacionGeneral('Nueva postulación recibida'));
                
                // Crear la postulación
                return Postulacion::create([
                    'user_id' => $user->id,
                    'convocatoria_id' => $convocatoriaId,
                    'estado_postulacion' => 'enviada'
                ]);
            });

            return response()->json(['message' => 'Postulación enviada correctamente', 'data' => $postulacion], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al crear la postulación.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function obtenerPostulaciones()
    {
        try {
            $postulaciones = Postulacion::with('usuarioPostulacion', 'convocatoriaPostulacion')->get();

            return response()->json(['postulaciones' => $postulaciones], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al obtener las postulaciones.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerPostulacionesUsuario(Request $request)
    {
        try {
            $postulaciones = Postulacion::where('user_id', $request->user()->id)
                ->with('convocatoriaPostulacion')
                ->get();

            return response()->json(['postulaciones' => $postulaciones], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al obtener las postulaciones del usuario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerPorConvocatoria($idConvocatoria)
    {
        try {
            $postulaciones = Postulacion::where('convocatoria_id', $idConvocatoria)
                ->with('usuarioPostulacion')
                ->get();

            return response()->json(['postulaciones' => $postulaciones], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al obtener las postulaciones por convocatoria.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function documentosPorUsuarioYConvocatoria($idConvocatoria, $idUsuario)
    {
        try {
            $postulacion = Postulacion::where('convocatoria_id', $idConvocatoria)
                ->where('user_id', $idUsuario)
                ->first();

            if (!$postulacion) {
                return response()->json([
                    'message' => 'El usuario no está postulado a esta convocatoria.'
                ], 404);
            }

            $usuario = User::with([
                'estudiosUsuario.documentosEstudio',
                'idiomasUsuario.documentosIdioma',
                'experienciasUsuario.documentosEXperiencia',
                'produccionAcademicaUsuario.documentosProduccionAcademica',
                'epsUsuario.documentosEps',
                'rutUsuario.documentosRut',
                'informacionContactoUsuario.documentosInformacionContacto',
            ])->findOrFail($idUsuario);

            return response()->json([
                'usuario' => $usuario->only([
                    'id',
                    'primer_nombre',
                    'segundo_nombre',
                    'primer_apellido',
                    'segundo_apellido',
                    'email'
                ]),
                'estudios' => $usuario->estudiosUsuario ? $usuario->estudiosUsuario->map(function ($estudio) {
                    return [
                        'estudio' => $estudio,
                        'documentos' => $estudio->documentosEstudio->map(function ($documento) {
                            return [
                                'id' => $documento->id_documento,
                                'nombre' => $documento->nombre,
                                'archivo' => asset('storage/' . $documento->archivo),
                            ];
                        }) ?? collect()
                    ];
                }) : collect(),
                'idiomas' => $usuario->idiomasUsuario ? $usuario->idiomasUsuario->map(function ($idioma) {
                    return [
                        'idioma' => $idioma,
                        'documentos' => $idioma->documentosIdioma->map(function ($documento) {
                            return [
                                'id' => $documento->id_documento,
                                'nombre' => $documento->nombre,
                                'archivo' => asset('storage/' . $documento->archivo),
                            ];
                        }) ?? collect()
                    ];
                }) : collect(),
                'experiencias' => $usuario->experienciasUsuario ? $usuario->experienciasUsuario->map(function ($experiencia) {
                    return [
                        'experiencia' => $experiencia,
                        'documentos' => $experiencia->documentosEXperiencia->map(function ($documento) {
                            return [
                                'id' => $documento->id_documento,
                                'nombre' => $documento->nombre,
                                'archivo' => asset('storage/' . $documento->archivo),
                            ];
                        }) ?? collect()
                    ];
                }) : collect(),
                'producciones' => $usuario->produccionAcademicaUsuario ? $usuario->produccionAcademicaUsuario->map(function ($produccion) {
                    return [
                        'produccion' => $produccion,
                        'documentos' => $produccion->documentosProduccionAcademica->map(function ($documento) {
                            return [
                                'id' => $documento->id_documento,
                                'nombre' => $documento->nombre,
                                'archivo' => asset('storage/' . $documento->archivo),
                            ];
                        }) ?? collect()
                    ];
                }) : collect(),
                'eps' => $usuario->epsUsuario ? [
                    'eps' => $usuario->epsUsuario,
                    'documentos' => $usuario->epsUsuario->documentosEps->map(function ($documento) {
                        return [
                            'id' => $documento->id_documento,
                            'nombre' => $documento->nombre,
                            'archivo' => asset('storage/' . $documento->archivo),
                        ];
                    }) ?? collect()
                ] : null,
                'rut' => $usuario->rutUsuario ? [
                    'rut' => $usuario->rutUsuario,
                    'documentos' => $usuario->rutUsuario->documentosRut->map(function ($documento) {
                        return [
                            'id' => $documento->id_documento,
                            'nombre' => $documento->nombre,
                            'archivo' => asset('storage/' . $documento->archivo),
                        ];
                    }) ?? collect()
                ] : null,
                'informacion_contacto' => $usuario->informacionContactoUsuario ? [
                    'contacto' => $usuario->informacionContactoUsuario,
                    'documentos' => $usuario->informacionContactoUsuario->documentosInformacionContacto->map(function ($documento) {
                        return [
                            'id' => $documento->id_documento,
                            'nombre' => $documento->nombre,
                            'archivo' => asset('storage/' . $documento->archivo),
                        ];
                    }) ?? collect()
                ] : null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al obtener los documentos del usuario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarEstadoPostulacion(Request $request, $idPostulacion)
    {
        try {
            $request->validate([
                'estado_postulacion' => 'required|in:' . implode(',', EstadoPostulacion::all()),
            ]);

            $postulacion = DB::transaction(function () use ($request, $idPostulacion) {
                $postulacion = Postulacion::find($idPostulacion);

                if (!$postulacion) {
                    throw new \Exception('Postulación no encontrada.', 404);
                }

                $postulacion->estado_postulacion = $request->estado_postulacion;
                $postulacion->save();

                $talentoHumano = User::roles(['Docente','Aspirante'])->get();
                Notification::send($talentoHumano, new NotificacionGeneral('Postulacion actualizada'));
                
                $talentoHumano = User::role('Talento Humano')->get();
                Notification::send($talentoHumano, new NotificacionGeneral('Postulacion actualizada'));
                

                return $postulacion;
            });

            return response()->json([
                'message' => 'Estado de postulación actualizado correctamente.',
                'postulacion' => $postulacion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el estado de la postulación.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function eliminarPostulacion($idPostulacion)
    {
        try {
            DB::transaction(function () use ($idPostulacion) {
                $postulacion = Postulacion::find($idPostulacion);

                if (!$postulacion) {
                    throw new \Exception('Postulación no encontrada.', 404);
                }

                $postulacion->delete();
            });

            return response()->json([
                'message' => 'Postulación eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al eliminar la postulación.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function eliminarPostulacionUsuario(Request $request, $id)
    {
        try {
            $postulacion = Postulacion::find($id);

            if (!$postulacion) {
                throw new \Exception('Postulación no encontrada.', 404);
            }

            if ($postulacion->user_id !== $request->user()->id) {
                throw new \Exception('No tienes permiso para eliminar esta postulación.', 403);
            }

            $postulacion->delete();

            return response()->json([
                'message' => 'Postulación eliminada correctamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al eliminar la postulación del usuario.',
                'error' => $e->getMessage()
            ],$e->getCode() ?: 500);
        }
    }
}
