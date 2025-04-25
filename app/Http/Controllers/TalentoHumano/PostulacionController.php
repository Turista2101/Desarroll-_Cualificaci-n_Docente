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

    public function generarHojaDeVidaPDF($idConvocatoria, $idUsuario)
    {
        try {
            // Verifica si el usuario está postulado a la convocatoria
            $postulacion = Postulacion::where('convocatoria_id', $idConvocatoria)
                ->where('user_id', $idUsuario)
                ->first();
    
            if (!$postulacion) {
                return response()->json([
                    'message' => 'El usuario no está postulado a esta convocatoria.'
                ], 404);
            }
    
            // Cargar usuario con relaciones necesarias
            $usuario = User::with([
                'fotoPerfilUsuario.documentosFotoPerfil',
                'informacionContactoUsuario.documentosInformacionContacto',
                'estudiosUsuario.documentosEstudio',
                'experienciasUsuario.documentosExperiencia',
                'epsUsuario.documentosEps',
                'idiomasUsuario.documentosIdioma',
                'rutUsuario.documentosRut',
                'produccionAcademicaUsuario.documentosProduccionAcademica',
            ])->findOrFail($idUsuario);
    
            // Crear instancia de FPDI (TCPDI)
            $pdf = new FPDI();  // Instancia FPDI correctamente
    
            // Añadir una página
            $pdf->AddPage();
    
            // Establecer fuente
            $pdf->SetFont('Helvetica', '', 12);
    
            // Verifica si el usuario tiene una foto de perfil
            if ($usuario->fotoPerfilUsuario && $usuario->fotoPerfilUsuario->documentosFotoPerfil) {
                // Obtener la ruta de la imagen
                $fotoRelativePath = $usuario->fotoPerfilUsuario->documentosFotoPerfil->first()->archivo ?? null;
    
                if (!$fotoRelativePath) {
                    throw new \Exception('No se encontró la ruta relativa de la foto de perfil.');
                }
    
                // Generar la ruta pública del archivo de la foto
                $fotoStoragePath = public_path('storage/' . $fotoRelativePath);  // Usar la ruta pública
    
                // Verificar si el archivo existe
                if (file_exists($fotoStoragePath)) {
                    // Coloca la foto de perfil centrada (ajusta las coordenadas y tamaño de la imagen)
                    $pdf->Image($fotoStoragePath, 70, 20, 50, 50, 'JPG');  // Ajusta el tamaño y la posición según sea necesario
                } else {
                    // Si la foto no está disponible, puedes poner una imagen por defecto o un mensaje
                    $pdf->Cell(0, 10, 'Foto de perfil no disponible', 0, 1);
                }
            }
    
            // Insertar el resto de la información del usuario
            $pdf->Ln(60); // Espacio después de la foto de perfil
    
            // Información personal
            $pdf->Cell(0, 10, 'Nombre: ' . $usuario->primer_nombre, 0, 1);
            $pdf->Cell(0, 10, 'Correo: ' . $usuario->email, 0, 1);
    
            // Agregar más detalles (ej. estudios, experiencia, etc.)
            $pdf->Ln(10);
            $pdf->Cell(0, 10, 'Estudios: ' . $usuario->estudiosUsuario->pluck('nombre')->implode(', '), 0, 1);
    
            // Anexar los documentos PDF relacionados con los estudios del usuario
            if ($usuario->estudiosUsuario) {
                foreach ($usuario->estudiosUsuario as $estudio) {
                    if ($estudio->documentosEstudio) {
                        foreach ($estudio->documentosEstudio as $documento) {
                            $pdfPath = public_path('storage/' . $documento->archivo); // Ruta del archivo PDF
    
                            if (file_exists($pdfPath)) {
                                // Convertir el archivo PDF a imagen usando Imagick
                                $imagick = new Imagick();
                                $imagick->readImage($pdfPath); // Leer el PDF
    
                                // Convertir cada página del PDF a una imagen
                                foreach ($imagick as $key => $page) {
                                    // Convertir la página a imagen (puedes ajustar la calidad si es necesario)
                                    $page->setImageFormat('jpeg');  // Cambiar el formato si es necesario
                                    $imagePath = storage_path('app/public/converted_page_' . $key . '.jpg');
                                    $page->writeImage($imagePath);
    
                                    // Agregar la imagen al PDF
                                    $pdf->AddPage();
                                    $pdf->Image($imagePath, 10, 10, 190, 0, 'JPG'); // Ajusta el tamaño y las coordenadas según sea necesario
                                }
                            } else {
                                // Si el archivo no existe, registrar un mensaje en el PDF
                                $pdf->AddPage();
                                $pdf->SetFont('Helvetica', '', 12);
                                $pdf->Cell(0, 10, 'El documento "' . $documento->nombre . '" no está disponible.', 0, 1);
                            }
                        }
                    }
                }
            }
    
            // Guardar el PDF o devolverlo al usuario
            $pdf->Output('hoja_de_vida_' . $idUsuario . '.pdf', 'I');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al generar el PDF.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    // public function generarHojaDeVidaPDF($id)
    // {
    //     // Cargar el usuario con las relaciones necesarias
    //     $usuario = User::with([
    //         'fotoPerfilUsuario.documentosFotoPerfil',
    //         'informacionContactoUsuario.documentosInformacionContacto',
    //         'estudiosUsuario.documentosEstudio',
    //         'experienciasUsuario.documentosExperiencia',
    //         'epsUsuario.documentosEps',
    //         'idiomasUsuario.documentosIdioma',
    //         'rutUsuario.documentosRut',
    //         'produccionAcademicaUsuario.documentosProduccionAcademica',
    //     ])->findOrFail($id);

    //     // Verificar si las relaciones existen y asignar un valor por defecto si no es así
    //     $usuario->informacionContactoUsuario = $usuario->informacionContactoUsuario ?? [];
    //     $usuario->fotoPerfilUsuario = $usuario->fotoPerfilUsuario ?? [];
    //     $usuario->estudiosUsuario = $usuario->estudiosUsuario ?? collect();
    //     $usuario->experienciasUsuario = $usuario->experienciasUsuario ?? collect();
    //     $usuario->epsUsuario = $usuario->epsUsuario ?? [];
    //     $usuario->idiomasUsuario = $usuario->idiomasUsuario ?? collect();
    //     $usuario->rutUsuario = $usuario->rutUsuario ?? [];
    //     $usuario->produccionAcademicaUsuario = $usuario->produccionAcademicaUsuario ?? collect();

    //     // Generar el PDF con la vista
    //     $pdf = Pdf::loadView('hoja_de_vida', compact('usuario'))->setPaper('A4', 'portrait');

    //     return $pdf->stream('hoja-de-vida.pdf');
    // }



    // public function generarHojaDeVidaPDF($idConvocatoria, $idUsuario)
    // {
    //     try {
    //         // Verifica si el usuario está postulado a la convocatoria
    //         $postulacion = Postulacion::where('convocatoria_id', $idConvocatoria)
    //             ->where('user_id', $idUsuario)
    //             ->first();

    //         if (!$postulacion) {
    //             return response()->json([
    //                 'message' => 'El usuario no está postulado a esta convocatoria.'
    //             ], 404);
    //         }

    //         // Cargar usuario con relaciones necesarias
    //         $usuario = User::with([
    //             'fotoPerfilUsuario.documentosFotoPerfil',
    //             'informacionContactoUsuario.documentosInformacionContacto',
    //             'estudiosUsuario.documentosEstudio',
    //             'experienciasUsuario.documentosExperiencia',
    //             'epsUsuario.documentosEps',
    //             'idiomasUsuario.documentosIdioma',
    //             'rutUsuario.documentosRut',
    //             'produccionAcademicaUsuario.documentosProduccionAcademica',
    //         ])->findOrFail($idUsuario);

    //         // Verificar relaciones (asignar colecciones vacías o valores por defecto)
    //         $usuario->informacionContactoUsuario = $usuario->informacionContactoUsuario ?? [];
    //         $usuario->fotoPerfilUsuario = $usuario->fotoPerfilUsuario ?? [];
    //         $usuario->estudiosUsuario = $usuario->estudiosUsuario ?? collect();
    //         $usuario->experienciasUsuario = $usuario->experienciasUsuario ?? collect();
    //         $usuario->epsUsuario = $usuario->epsUsuario ?? [];
    //         $usuario->idiomasUsuario = $usuario->idiomasUsuario ?? collect();
    //         $usuario->rutUsuario = $usuario->rutUsuario ?? [];
    //         $usuario->produccionAcademicaUsuario = $usuario->produccionAcademicaUsuario ?? collect();

    //         // Generar el PDF con la vista y pasar el usuario como variable
    //         $pdf = Pdf::loadView('hoja_de_vida', compact('usuario'))->setPaper('A4', 'portrait');

    //         return $pdf->stream('hoja-de-vida.pdf');
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Ocurrió un error al generar el PDF.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function documentosPorUsuarioYConvocatoria($idConvocatoria, $idUsuario)
    // {
    //     try {
    //         $postulacion = Postulacion::where('convocatoria_id', $idConvocatoria)
    //             ->where('user_id', $idUsuario)
    //             ->first();

    //         if (!$postulacion) {
    //             return response()->json([
    //                 'message' => 'El usuario no está postulado a esta convocatoria.'
    //             ], 404);
    //         }

    //         $usuario = User::with([
    //             'estudiosUsuario.documentosEstudio',
    //             'idiomasUsuario.documentosIdioma',
    //             'experienciasUsuario.documentosEXperiencia',
    //             'produccionAcademicaUsuario.documentosProduccionAcademica',
    //             'epsUsuario.documentosEps',
    //             'rutUsuario.documentosRut',
    //             'informacionContactoUsuario.documentosInformacionContacto',
    //         ])->findOrFail($idUsuario);

    //         return response()->json([
    //             'usuario' => $usuario->only([
    //                 'id',
    //                 'primer_nombre',
    //                 'segundo_nombre',
    //                 'primer_apellido',
    //                 'segundo_apellido',
    //                 'email'
    //             ]),
    //             'estudios' => $usuario->estudiosUsuario ? $usuario->estudiosUsuario->map(function ($estudio) {
    //                 return [
    //                     'estudio' => $estudio,
    //                     'documentos' => $estudio->documentosEstudio->map(function ($documento) {
    //                         return [
    //                             'id' => $documento->id_documento,
    //                             'nombre' => $documento->nombre,
    //                             'archivo' => asset('storage/' . $documento->archivo),
    //                         };
    //                     }) ?? collect()
    //                 ];
    //             }) : collect(),
    //             'idiomas' => $usuario->idiomasUsuario ? $usuario->idiomasUsuario->map(function ($idioma) {
    //                 return [
    //                     'idioma' => $idioma,
    //                     'documentos' => $idioma->documentosIdioma->map(function ($documento) {
    //                         return [
    //                             'id' => $documento->id_documento,
    //                             'nombre' => $documento->nombre,
    //                             'archivo' => asset('storage/' . $documento->archivo),
    //                         ];
    //                     }) ?? collect()
    //                 ];
    //             }) : collect(),
    //             'experiencias' => $usuario->experienciasUsuario ? $usuario->experienciasUsuario->map(function ($experiencia) {
    //                 return [
    //                     'experiencia' => $experiencia,
    //                     'documentos' => $experiencia->documentosEXperiencia->map(function ($documento) {
    //                         return [
    //                             'id' => $documento->id_documento,
    //                             'nombre' => $documento->nombre,
    //                             'archivo' => asset('storage/' . $documento->archivo),
    //                         ];
    //                     }) ?? collect()
    //                 ];
    //             }) : collect(),
    //             'producciones' => $usuario->produccionAcademicaUsuario ? $usuario->produccionAcademicaUsuario->map(function ($produccion) {
    //                 return [
    //                     'produccion' => $produccion,
    //                     'documentos' => $produccion->documentosProduccionAcademica->map(function ($documento) {
    //                         return [
    //                             'id' => $documento->id_documento,
    //                             'nombre' => $documento->nombre,
    //                             'archivo' => asset('storage/' . $documento->archivo),
    //                         ];
    //                     }) ?? collect()
    //                 ];
    //             }) : collect(),
    //             'eps' => $usuario->epsUsuario ? [
    //                 'eps' => $usuario->epsUsuario,
    //                 'documentos' => $usuario->epsUsuario->documentosEps->map(function ($documento) {
    //                     return [
    //                         'id' => $documento->id_documento,
    //                         'nombre' => $documento->nombre,
    //                         'archivo' => asset('storage/' . $documento->archivo),
    //                     ];
    //                 }) ?? collect()
    //             ] : null,
    //             'rut' => $usuario->rutUsuario ? [
    //                 'rut' => $usuario->rutUsuario,
    //                 'documentos' => $usuario->rutUsuario->documentosRut->map(function ($documento) {
    //                     return [
    //                         'id' => $documento->id_documento,
    //                         'nombre' => $documento->nombre,
    //                         'archivo' => asset('storage/' . $documento->archivo),
    //                     ];
    //                 }) ?? collect()
    //             ] : null,
    //             'informacion_contacto' => $usuario->informacionContactoUsuario ? [
    //                 'contacto' => $usuario->informacionContactoUsuario,
    //                 'documentos' => $usuario->informacionContactoUsuario->documentosInformacionContacto->map(function ($documento) {
    //                     return [
    //                         'id' => $documento->id_documento,
    //                         'nombre' => $documento->nombre,
    //                         'archivo' => asset('storage/' . $documento->archivo),
    //                     ];
    //                 }) ?? collect()
    //             ] : null,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Ocurrió un error al obtener los documentos del usuario.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

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

                $talentoHumano = User::roles(['Docente', 'Aspirante'])->get();
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
            ], $e->getCode() ?: 500);
        }
    }
}
