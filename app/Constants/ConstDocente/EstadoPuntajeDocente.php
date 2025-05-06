<?php

namespace App\Constants\ConstDocente;

class EstadoPuntajeDocente
{
    public const PENDIENTE = 'Pendiente';
    public const APROVADO = 'Aprobado';
    public const RECHAZADO  = 'Rechazado';

    public static function all(): array
    {
        return [
            self::PENDIENTE,
            self::APROVADO,
            self::RECHAZADO
        ];
    }
}