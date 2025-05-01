<?php

namespace App\Services;

use App\Models\Usuario\User;

class RevertirDocumentosService
{
    public function revertirDocumentosDeUsuario(User $user): string
    {
        $relaciones = [
            'estudiosUsuario'             => 'documentosEstudio',
            'experienciasUsuario'         => 'documentosExperiencia',
            'idiomasUsuario'              => 'documentosIdioma',
            'produccionAcademicaUsuario'  => 'documentosProduccionAcademica',
            'epsUsuario'                  => 'documentosEps',
            'informacionContactoUsuario'  => 'documentosInformacionContacto',
            'rutUsuario'                  => 'documentosRut',
            'fotoPerfilUsuario'           => 'documentosFotoPerfil',
        ];

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
                        ->where('estado', 'aprobado')
                        ->update(['estado' => 'pendiente']);
                }
            }
        }

        // Revertir documentos directamente en el usuario
        $user->documentosUser()
            ->where('estado', 'aprobado')
            ->update(['estado' => 'pendiente']);

        return 'Los documentos han sido devueltos al estado pendiente correctamente.';
    }
}