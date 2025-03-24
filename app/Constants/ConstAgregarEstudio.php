<?php

namespace App\Constants;

// Tipos de constantes de estuido, contiene las diferentes tipos de estudios
// si desea agregar uno nuevo hagalo desde aqui, y se reflejara en la base de datos

class Estudio
{
    // Tipos de estudios
    public const CURSO_PROGRAMADO_O_CAPACITACION = 'Curso programado o capacitación';
    public const PREGRADO = 'Pregrado';
    public const PREGRADO_EN_MEDICINA_HUMANA_O_COMPOSICION_MUSICAL = 'Pregrado en medicina humana o composición musical';
    public const ESPECIALIZACION = 'Especialización';
    public const ESPECIALIZACION_EN_MEDICINA_HUMANA_Y_ODONTOLOGIA = 'Especialización en medicina humana y odontología';
    public const MAESTRIA = 'Maestría';
    public const DOCTORADO = 'Doctorado';
    public const POSTDOCTORADO = 'Postdoctorado';
    public const TECNICO = 'Técnico';
    public const TECNOLOGICO = 'Tecnológico';
    public const DIPLOMADO = 'Diplomado';
    public const CERTIFICACION = 'Certificación';

    // Retorna todos los tipos de estudios
    public static function all(): array
    {
        return [
            self::CURSO_PROGRAMADO_O_CAPACITACION,
            self::PREGRADO,
            self::PREGRADO_EN_MEDICINA_HUMANA_O_COMPOSICION_MUSICAL,
            self::ESPECIALIZACION,
            self::ESPECIALIZACION_EN_MEDICINA_HUMANA_Y_ODONTOLOGIA,
            self::MAESTRIA,
            self::DOCTORADO,
            self::POSTDOCTORADO,
            self::TECNICO,
            self::TECNOLOGICO,
            self::DIPLOMADO,
            self::CERTIFICACION
        ];
    }
}
