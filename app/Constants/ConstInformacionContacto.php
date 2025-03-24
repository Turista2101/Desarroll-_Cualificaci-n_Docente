<?php

namespace App\Constants;

// Tipos de constantes de informacion de contacto, contiene los diferentes tipos de seleccion que usamos en  informacion de contacto
// si desea agregar uno nuevo hagalo desde aqui, y se reflejara en la base de datos


class CategoriaLibretaMilitar
{
    // Tipos de categorias de libreta militar
    public const PRIMERA_CLASE = 'Primera clase';
    public const SEGUNDA_CLASE = 'Segunda clase';
    public const NO_TIENE = 'No tiene';
    // Retorna todos los tipos de categorias de libreta militar
    public static function all(): array
    {
        return [
            self::PRIMERA_CLASE,
            self::SEGUNDA_CLASE,
            self::NO_TIENE
        ];
    }
}

