<?php

namespace App\Constants\ConstInformacionContacto;

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
