<?php

namespace App\Constants\ConstUsuario;

class EstadoCivil
{
    // Tipos de estados civiles
    public const SOLTERO = 'Soltero';
    public const CASADO = 'Casado';
    public const DIVORCIADO = 'Divorciado';
    public const VIUDO = 'Viudo';
    // Retorna todos los tipos de estados civiles
    public static function all(): array
    {
        return [
            self::SOLTERO,
            self::CASADO,
            self::DIVORCIADO,
            self::VIUDO
        ];
    }
}