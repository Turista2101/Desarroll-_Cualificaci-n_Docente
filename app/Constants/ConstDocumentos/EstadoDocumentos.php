<?php

namespace App\Constants\ConstDocente;

class EstadoDocumentos
{
    public const PENDIENTE = 'pendiente';
    public const APROVADO = 'aprobado';
    public const RECHAZADO  = 'rechazado';

    public static function all(): array
    {
        return [
            self::PENDIENTE,
            self::APROVADO,
            self::RECHAZADO
        ];
    }
}