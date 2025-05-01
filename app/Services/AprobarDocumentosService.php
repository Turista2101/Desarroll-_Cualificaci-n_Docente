<?php

namespace App\Services;

use App\Models\Usuario\User;

class AprobarDocumentosService
{
    public function aprobarDocumentosDeUsuario(User $user): string
    {
        // Relaciones con modelos intermedios (excluyendo Producción Académica)
        $relaciones = [
            'estudiosUsuario'             => 'documentosEstudio',
            'experienciasUsuario'         => 'documentosExperiencia',
            'idiomasUsuario'              => 'documentosIdioma',
            'epsUsuario'                  => 'documentosEps',
            'informacionContactoUsuario'  => 'documentosInformacionContacto',
            'rutUsuario'                  => 'documentosRut',
            'fotoPerfilUsuario'           => 'documentosFotoPerfil',
        ];

        // Aprobar documentos en modelos intermedios
        foreach ($relaciones as $relacion => $metodoDocumentos) {
            $user->loadMissing($relacion);
            $relacionData = $user->{$relacion};

            if (is_null($relacionData)) {
                continue;
            }

            $modelos = is_iterable($relacionData) ? $relacionData : [$relacionData];

            foreach ($modelos as $modelo) {
                if (method_exists($modelo, $metodoDocumentos)) {
                    $modelo->{$metodoDocumentos}()
                        ->where('estado', 'pendiente')
                        ->update(['estado' => 'aprobado']);
                }
            }
        }

        // Aprobar documentos polimórficos directamente del usuario
        $user->documentosUser()
            ->where('estado', 'pendiente')
            ->update(['estado' => 'aprobado']);

        return 'Los documentos han sido aprobados correctamente.';
    }
}
