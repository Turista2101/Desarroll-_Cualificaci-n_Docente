<?php

namespace App\Constants\ConstAgregarExperiencia;

class TrabajoActual
{
    // Tipos de experiencias
    public const SI = 'Si';
    public const NO = 'No';

    // Retorna todos los tipos de experiencias
    public static function all(): array
    {
        return [
            self::SI,
            self::NO,
        ];
    }
}