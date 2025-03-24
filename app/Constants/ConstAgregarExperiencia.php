<?php

namespace App\Constants;

// Tipos de constantes de experiencia, contiene las diferentes tipos de experiencias
// si desea agregar uno nuevo hagalo desde aqui, y se reflejara en la base de datos

class Experiencia
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
