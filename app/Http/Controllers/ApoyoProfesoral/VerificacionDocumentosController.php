<?php

namespace App\Http\Controllers\ApoyoProfesoral;

use App\Constants\ConstDocumentos\EstadoDocumentos;
use App\Models\Aspirante\Documento;
use App\Models\Usuario\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VerificacionDocumentosController
{
    /**
     * Relaciones entre categorías de documentos y sus modelos asociados.
     */
    protected array $relaciones = [
        'estudiosUsuario' => 'documentosEstudio',
        'experienciasUsuario' => 'documentosExperiencia',
        'idiomasUsuario' => 'documentosIdioma',
        'rutUsuario' => 'documentosRut',
        'informacionContactoUsuario' => 'documentosInformacionContacto',
        'epsUsuario' => 'documentosEps',
    ];


    private function prepararRelacionesFiltradas($estado)
    {
        $relacionesFiltradas = [];
        foreach ($this->relaciones as $relacionPadre => $relacionDocumentos) {
            $relacionesFiltradas[$relacionPadre . '.' . $relacionDocumentos] = function ($query) use ($estado) {
                $query->where('estado', $estado);
            };
        }
        return $relacionesFiltradas;
    }

    
    private function aplicarFiltrosPorEstado($query, $estado)
    {
        foreach ($this->relaciones as $relacionPadre => $relacionDocumentos) {
            $query->orWhereHas($relacionPadre . '.' . $relacionDocumentos, function ($q) use ($estado) {
                $q->where('estado', $estado);
            });
        }

        $query->orWhereHas('documentosUser', function ($q) use ($estado) {
            $q->where('estado', $estado);
        });
    }

    private function agregarUrlADocumentos($usuarios)
    {
        foreach ($usuarios as $usuario) {
            foreach ($this->relaciones as $relacionPadre => $relacionDocumentos) {
                foreach ($usuario->$relacionPadre ?? [] as $elemento) {
                    foreach ($elemento->$relacionDocumentos ?? [] as $documento) {
                        $documento->archivo_url = Storage::url($documento->archivo);
                    }
                }
            }

            foreach ($usuario->documentosUser ?? [] as $documentoUser) {
                $documentoUser->archivo_url = Storage::url($documentoUser->archivo);
            }
        }
    }

    public function obtenerDocumentosPorEstado($estado)
    {
        try {
            $relacionesFiltradas = $this->prepararRelacionesFiltradas($estado);

            $usuarios = User::role('Docente')
                ->with($relacionesFiltradas)
                ->where(function ($query) use ($estado) {
                    $this->aplicarFiltrosPorEstado($query, $estado);
                })
                ->get();

            $this->agregarUrlADocumentos($usuarios);

            return response()->json([
                'data' => $usuarios,
                'message' => $usuarios->isEmpty() ? 'No se encontraron documentos con ese estado.' : ''
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener documentos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function obtenerDocumentosPorEstado($estado)
    // {
    //     try {
    //         $relacionesFiltradas = [];

    //         // Prepara las relaciones para hacer eager loading con condición por estado
    //         foreach ($this->relaciones as $relacionPadre => $relacionDocumentos) {
    //             $relacionesFiltradas[$relacionPadre . '.' . $relacionDocumentos] = function ($query) use ($estado) {
    //                 $query->where('estado', $estado);
    //             };
    //         }

    //         // Obtener solo usuarios que tienen al menos un documento con el estado dado en cualquier relación
    //         $usuarios = User::role('Docente')
    //             ->with($relacionesFiltradas)
    //             ->where(function ($query) use ($estado) {
    //                 // Filtrar por estado en cada una de las relaciones definidas
    //                 foreach ($this->relaciones as $relacionPadre => $relacionDocumentos) {
    //                     $query->orWhereHas($relacionPadre . '.' . $relacionDocumentos, function ($q) use ($estado) {
    //                         $q->where('estado', $estado);
    //                     });
    //                 }

    //                 // Filtrar los documentos directamente relacionados con el usuario (documentosUser)
    //                 $query->orWhereHas('documentosUser', function ($q) use ($estado) {
    //                     $q->where('estado', $estado);
    //                 });
    //             })
    //             ->get();

    //         // Agregar URL a cada documento
    //         foreach ($usuarios as $usuario) {
    //             // Filtramos documentos relacionados con las relaciones definidas
    //             foreach ($this->relaciones as $relacionPadre => $relacionDocumentos) {
    //                 $elementos = $usuario->$relacionPadre ?? [];

    //                 foreach ($elementos as $elemento) {
    //                     foreach ($elemento->$relacionDocumentos ?? [] as $documento) {
    //                         $documento->archivo_url = Storage::url($documento->archivo);
    //                     }
    //                 }
    //             }

    //             // Filtrar y agregar URL a los documentos directamente relacionados con el usuario (documentosUser)
    //             foreach ($usuario->documentosUser ?? [] as $documentoUser) {
    //                 $documentoUser->archivo_url = Storage::url($documentoUser->archivo);
    //             }
    //         }

    //         return response()->json([
    //             'data' => $usuarios,
    //             'message' => $usuarios->isEmpty() ? 'No se encontraron documentos con ese estado.' : ''
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Error al obtener documentos.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * Actualiza el estado de un documento específico.
     */
    public function actualizarEstadoDocumento(Request $request, $documento_id)
    {
        try {
            $request->validate([
                'estado' => ['required', Rule::in(EstadoDocumentos::all())],
            ]);

            $documento = Documento::findOrFail($documento_id);
            $documento->estado = $request->estado;
            $documento->save();

            return response()->json([
                'message' => 'Estado del documento actualizado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el estado del documento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
