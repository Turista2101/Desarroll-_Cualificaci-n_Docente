<?php

namespace App\Http\Controllers\TalentoHumano;

use App\Http\Controllers\Controller;
use App\Models\User; // o el modelo que uses para usuarios
use App\Notifications\NotificacionGeneral;
use Illuminate\Support\Facades\Notification;

class NotificacionController
{
    public static function nuevaConvocatoria($usuarios)
    {
        $mensaje = 'Se ha publicado una nueva convocatoria.';
        Notification::send($usuarios, new NotificacionGeneral($mensaje));
    }

    public static function cambioEstadoPostulacion($usuario, $estado)
    {
        $mensaje = "Tu postulación ha cambiado de estado a: $estado.";
        $usuario->notify(new NotificacionGeneral($mensaje));
    }

    public static function nuevaPostulacion($admin)
    {
        $mensaje = 'Un aspirante se ha postulado a una convocatoria.';
        $admin->notify(new NotificacionGeneral($mensaje));
    }

    public static function nuevaContratacion($usuario)
    {
        $mensaje = 'Has sido contratado. ¡Felicitaciones!';
        $usuario->notify(new NotificacionGeneral($mensaje));
    }
}
