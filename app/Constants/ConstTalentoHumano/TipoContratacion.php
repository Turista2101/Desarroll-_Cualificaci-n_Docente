<?php

namespace App\Constants\ConstTalentoHumano;

class TipoContratacion
{
    public const PLANTA = 'Planta';
    public const OCASIONAL = 'Ocasional';
    public const CATEDRA = 'Cátedra';

    public static function all(): array
    {
        return [
            self::PLANTA,
            self::OCASIONAL,
            self::CATEDRA
        ];
       
    }
}