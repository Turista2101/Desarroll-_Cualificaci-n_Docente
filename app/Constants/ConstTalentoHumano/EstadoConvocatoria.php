<?php

namespace App\Constants\ConstTalentoHumano;

class EstadoConvocatoria
{
    public const ABIERTA = 'Abierta';
    public const CERRADA = 'Cerrada';
    public const FINALIZADA  = 'Finalizada';

    public static function all(): array
    {
        return [
            self::ABIERTA,
            self::CERRADA,
            self::FINALIZADA
        ];
    }
}