<?php

namespace App\Constants;

// Tipos de constantes de identificacion, contiene los diferentes tipos de identificacion
// si desea agregar uno nuevo hagalo desde aqui, y se reflejara en la base de datos


class TipoIdentificacion
{
    // Tipos de identificacion
    public const CEDULA_DE_CIUDADANIA = 'Cédula de ciudadanía';
    public const CEDULA_DE_EXTRANJERIA = 'Cédula de extranjería';
    public const NUMERO_UNICO_IDENTIFICACION_PERSONAL = 'Número único de identificación personal';
    public const PASAPORTE = 'Pasaporte';
    public const REGISTRO_CIVIL = 'Registro civil';
    public const NUMERO_POR_SECRETARIA_DE_EDUCACION = 'Número por secretaría de educación';
    public const SERVICIO_NACIONAL_DE_PRUEBAS = 'Servicio nacional de pruebas';
    public const TARJETA_DE_IDENTIDAD = 'Tarjeta de identidad';
    public const TARJETA_PROFESIONAL = 'Tarjeta profesional';

    // Retorna todos los tipos de identificacion
    public static function all() : array
    {
        return [
            self::CEDULA_DE_CIUDADANIA,
            self::CEDULA_DE_EXTRANJERIA,
            self::NUMERO_UNICO_IDENTIFICACION_PERSONAL,
            self::PASAPORTE,
            self::REGISTRO_CIVIL,
            self::NUMERO_POR_SECRETARIA_DE_EDUCACION,
            self::SERVICIO_NACIONAL_DE_PRUEBAS,
            self::TARJETA_DE_IDENTIDAD,
            self::TARJETA_PROFESIONAL
        ];
    }

}


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