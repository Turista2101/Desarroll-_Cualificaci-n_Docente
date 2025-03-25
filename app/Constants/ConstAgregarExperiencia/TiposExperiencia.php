<?php

namespace App\Constants\ConstAgregarExperiencia;

class TiposExperiencia
{
    // Tipos de experiencias
    public const INVESTIGACION = 'Investigación';
    public const DOCENCIA_UNIVERSITARIA = 'Docencia universitaria';
    public const DOCENCIA_NO_UNIVERSITARIA = 'Docencia no universitaria';
    public const PROFESORAL = 'Profesoral';
    public const DIRECCION_ACADEMICA = 'Dirección académica';

    // Retorna todos los tipos de experiencias
    public static function all(): array
    {
        return [
            self::INVESTIGACION,
            self::DOCENCIA_UNIVERSITARIA,
            self::DOCENCIA_NO_UNIVERSITARIA,
            self::PROFESORAL,
            self::DIRECCION_ACADEMICA
        ];
    }

}
