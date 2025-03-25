<?php

namespace App\Constants\ConstEps;

class EstadoAfiliacion
{
    // Estados de afiliacion
    public const ACTIVO = 'Activo';
    public const INACTIVO = 'Inactivo';
    public const SUSPENDIDO = 'Suspendido';
    // Retorna todos los estados de afiliacion
    public static function all(): array
    {
        return [
            self::ACTIVO,
            self::INACTIVO,
            self::SUSPENDIDO
        ];
    }
}