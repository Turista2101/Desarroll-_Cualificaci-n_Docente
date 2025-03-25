<?php

namespace App\Constants\ConstDocumentos;

class TipoDocumento
{
    public const RUT = 'RUT';
    public const EPS = 'EPS';
    public const CEDULA = 'Cédula';

    public static function all(): array
    {
        return [
            self::RUT,
            self::EPS,
            self::CEDULA
        ];
    }
}