<?php

namespace App\Constants\ConstTalentoHumano;
// Esta clase define los posibles estados de una postulación en el proceso de contratación
class EstadoPostulacion
{
    // Estado cuando la postulación ha sido enviada pero aún no ha sido evaluada
    public const ENVIADA = 'Enviada';
    // Estado cuando la postulación ha sido aceptada y el postulante avanza al siguiente paso
    public const ACEPTADA = 'Aceptada';
    // Estado cuando la postulación ha sido rechazada y el postulante no continúa en el proceso
    public const RECHAZADA  = 'Rechazada';

    // Retorna todos los estados de postulación disponibles como un arreglo
    public static function all(): array
    {
        return [
            self::ENVIADA,
            self::ACEPTADA,
            self::RECHAZADA
        ];
    }
}