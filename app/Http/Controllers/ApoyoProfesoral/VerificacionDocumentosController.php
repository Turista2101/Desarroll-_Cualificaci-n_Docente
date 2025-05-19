<?php

namespace App\Http\Controllers\ApoyoProfesoral;

use App\Constants\ConstDocumentos\EstadoDocumentos;
use App\Models\Aspirante\Documento;
use App\Models\Usuario\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
        // Relación directa del usuario (polimórfica)
        'usuario'                 => 'documentosUser',
    ];


    private function prepararRelacionesFiltradas($estado)
    {
        $relacionesFiltradas = [];

        foreach ($this->relaciones as $relacionPadre => $relacionDocumentos) {
            // Saltar la relación directa del usuario
            if ($relacionPadre === 'usuario') {
                continue;
            }
            $relacionesFiltradas[$relacionPadre . '.' . $relacionDocumentos] = function ($query) use ($estado) {
                $query->where('estado', $estado);
            };
        }

        return $relacionesFiltradas;
    }


    private function aplicarFiltrosPorEstado($query, $estado)
    {
        foreach ($this->relaciones as $relacionPadre => $relacionDocumentos) {
            if ($relacionPadre === 'usuario') {
                $query->orWhereHas('documentosUser', function ($q) use ($estado) {
                    $q->where('estado', $estado);
                });
            } else {
                $query->orWhereHas($relacionPadre . '.' . $relacionDocumentos, function ($q) use ($estado) {
                    $q->where('estado', $estado);
                });
            }
        }
    }


    private function agregarUrlADocumentos($usuarios)
    {
        foreach ($usuarios as $usuario) {
            foreach ($this->relaciones as $relacionPadre => $relacionDocumentos) {
                if ($relacionPadre === 'usuario') {
                    $this->agregarUrlADocumentosDirectos($usuario);
                } else {
                    $this->agregarUrlADocumentosRelacionados($usuario, $relacionPadre, $relacionDocumentos);
                }
            }
        }
    }

    private function agregarUrlADocumentosDirectos($usuario): void
    {
        foreach ($usuario->documentosUser ?? [] as $documentoUser) {
            $documentoUser->archivo_url = Storage::url($documentoUser->archivo);
        }
    }

    private function agregarUrlADocumentosRelacionados($usuario, $relacionPadre, $relacionDocumentos): void
    {
        foreach ($usuario->$relacionPadre ?? [] as $elemento) {
            foreach ($elemento->$relacionDocumentos ?? [] as $documento) {
                $documento->archivo_url = Storage::url($documento->archivo);
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


    public function listarDocentes()
    {
        try {
            $docentes = User::role('Docente')
                ->select(
                    'id',
                    DB::raw("CONCAT(primer_nombre, ' ', segundo_nombre, ' ', primer_apellido, ' ', segundo_apellido) AS nombre_completo"),
                    'email',
                    'numero_identificacion'
                ) // Solo los campos necesarios
                ->get();

            return response()->json([
                'data' => $docentes,
                'message' => $docentes->isEmpty() ? 'No hay docentes registrados.' : ''
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los docentes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verDocumentosPorDocente($user_id)
    {
        try {
            $usuario = User::with(array_merge(
                // Cargar relaciones anidadas
                collect($this->relaciones)
                    ->reject(fn($relacionDocumentos, $relacionPadre) => $relacionPadre === 'usuario')
                    ->mapWithKeys(fn($relacionDocumentos, $relacionPadre) => [
                        $relacionPadre . '.' . $relacionDocumentos => fn($q) => $q,
                    ])
                    ->toArray(),
                // Cargar documentosUser directamente
                ['documentosUser']
            ))->findOrFail($user_id);

            $this->agregarUrlADocumentos([$usuario]);

            return response()->json([
                'usuario' => $usuario,
                'message' => 'Documentos cargados correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los documentos del usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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
