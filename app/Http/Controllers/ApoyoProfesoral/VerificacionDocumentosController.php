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
    public function obtenerEstudiosPendientes()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'estudiosUsuario',
            'documentosEstudio',
            'pendiente'

        );
    }

    public function obtenerExperineciasPendientes()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'experienciasUsuario',
            'documentosExperiencia',
            'pendiente'

        );
    }

    public function obtenerIdiomasPendientes()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'idiomasUsuario',
            'documentosIdioma',
            'pendiente'

        );
    }

    public function obtenerRutPendientes()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'rutUsuario',
            'documentosRut',
            'pendiente'

        );
    }

    public function obtenerLibretaMilitarPendientes()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'informacionContactoUsuario',
            'documentosInformacionContacto',
            'pendiente'


        );
    }

    public function obtenerEpsPendientes()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'epsUsuario',
            'documentosEps',
            'pendiente'

        );
    }

    public function obtenerIndenitificacionPendientes()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'user',
            'documentosUser',
            'pendiente'
        );
    }

    //obtener documentos aprobados
    public function obtenerEstudiosAprobados()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'estudiosUsuario',
            'documentosEstudio',
            'aprobado'

        );
    }

    public function obtenerExperineciasAprobados()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'experienciasUsuario',
            'documentosExperiencia',
            'aprobado'

        );
    }

    public function obtenerIdiomasAprobados()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'idiomasUsuario',
            'documentosIdioma',
            'aprobado'

        );
    }

    public function obtenerRutAprobados()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'rutUsuario',
            'documentosRut',
            'aprobado'

        );
    }

    public function obtenerLibretaMilitarAprobados()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'informacionContactoUsuario',
            'documentosInformacionContacto',
            'aprobado'


        );
    }

    public function obtenerEpsAprobados()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'epsUsuario',
            'documentosEps',
            'aprobado'

        );
    }

    public function obtenerIndenitificacionAprobados()
    {
        return $this->obtenerDocumentosPorRelacionYEstado(
            'user',
            'documentosUser',
            'aprobado'
        );
    }

    protected function obtenerDocumentosPorRelacionYEstado($relacionPadre, $relacionDocumentos, $estado)
    {
        try {
            $usuarios = User::role('Docente')
                ->with([$relacionPadre . '.' . $relacionDocumentos => function ($query) use ($estado) {
                    $query->where('estado', $estado);
                }])
                ->whereHas($relacionPadre . '.' . $relacionDocumentos, function ($query) use ($estado) {
                    $query->where('estado', $estado);
                })
                ->get();

            if ($usuarios->isEmpty()) {
                return response()->json([
                    'message' => 'Aún no hay documentos por aprobar.',
                    'data' => []
                ], 200);
            }

            foreach ($usuarios as $usuario) {
                foreach ($usuario->$relacionPadre as $elemento) {
                    foreach ($elemento->$relacionDocumentos as $documento) {
                        $documento->archivo_url = Storage::url($documento->archivo);
                    }
                }
            }

            return response()->json(['data' => $usuarios], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener documentos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function actualizarEstadoDocumento(Request $request, $documento_id)
    {
        try {
            $request->validate([
                'estado' => ['required', Rule::in(EstadoDocumentos::all())], // o ['aprobado', 'rechazado', ...]
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
