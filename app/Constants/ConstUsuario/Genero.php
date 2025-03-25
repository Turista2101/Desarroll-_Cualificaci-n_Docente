<?php

namespace App\Constants\ConstUsuario;

class Genero
{
    // Tipos de generos
    public const MASCULINO = 'Masculino';
    public const FEMENINO = 'Femenino';
    public const OTRO = 'Otro';
    // Retorna todos los tipos de generos
    public static function all(): array
    {
        return [
            self::MASCULINO,
            self::FEMENINO,
            self::OTRO
        ];
    }
}