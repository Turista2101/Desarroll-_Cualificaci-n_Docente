<?php

namespace App\Constants\ConstTalentoHumano;

class EstadoPostulacion
{
    public const ENVIADA = 'Enviada';
    public const ACEPTADA = 'Aceptada';
    public const RECHAZADA  = 'Rechazada';

    public static function all(): array
    {
        return [
            self::ENVIADA,
            self::ACEPTADA,
            self::RECHAZADA
        ];
    }
}