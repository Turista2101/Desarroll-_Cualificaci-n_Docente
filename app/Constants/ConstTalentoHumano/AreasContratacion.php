<?php

namespace App\Constants\ConstTalentoHumano;

class AreasContratacion
{
    public const FACULTAD_DE_CIENCIAS_ADMINISTRATIVAS_CONTABLES_Y_ECONOMICAS = 'Facultad de Ciencias Administrativas, Contables y Economicas';
    public const FACULTAD_DE_CIENCIAS_AMBIENTALES_Y_DESARROLLO_SOSTENIBLE = 'Facultad de Ciencias Ambientales y Desarrollo Sostenible';
    public const FACULTAD_DE_DERECHO_CIENCIAS_SOCIALES_Y_POLITICAS  = 'Facultad de Derecho, Ciencias Sociales y Politicas';
    public const FACULTAD_DE_EDUCACION = 'Facultad de Educacion';
    public const FACULTAD_DE_INGENIERIA = 'Facultad de Ingenieria';

    public static function all(): array
    {
        return [
            self::FACULTAD_DE_CIENCIAS_ADMINISTRATIVAS_CONTABLES_Y_ECONOMICAS,
            self::FACULTAD_DE_CIENCIAS_AMBIENTALES_Y_DESARROLLO_SOSTENIBLE,
            self::FACULTAD_DE_DERECHO_CIENCIAS_SOCIALES_Y_POLITICAS,
            self::FACULTAD_DE_EDUCACION,
            self::FACULTAD_DE_INGENIERIA
        ];
    }
}