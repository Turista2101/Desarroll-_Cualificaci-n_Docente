<?php

namespace App\Constants\ConstEps;

class TipoAfiliacion
{
    // Tipos de afiliacion
    public const CONTRIBUTIVO = 'Contributivo';
    public const SUBSIDIADO = 'Subsidiado';
    public const VINCULADO = 'Vinculado';
    public const ESPECIAL = 'Especial';
    public const EXCEPCION = 'Excepción';
    // Retorna todos los tipos de afiliacion
    public static function all(): array
    {
        return [
            self::CONTRIBUTIVO,
            self::SUBSIDIADO,
            self::VINCULADO,
            self::ESPECIAL,
            self::EXCEPCION
        ];
    }
}
