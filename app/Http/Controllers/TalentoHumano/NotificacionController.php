<?php
// Define el espacio de nombres (namespace) del controlador dentro del módulo TalentoHumano

namespace App\Http\Controllers\TalentoHumano;
// Importa el controlador base de Laravel
use App\Http\Controllers\Controller;
// Importa el modelo User (o el modelo correspondiente de usuarios que estés usando)
use App\Models\User; // o el modelo que uses para usuarios
// Importa la clase de notificación personalizada
use App\Notifications\NotificacionGeneral;
// Importa la fachada Notification de Laravel para enviar notificaciones a múltiples usuarios
use Illuminate\Support\Facades\Notification;
// Define la clase NotificacionController
class NotificacionController
{
    // Método estático que notifica a un conjunto de usuarios sobre una nueva convocatoria
    public static function nuevaConvocatoria($usuarios)
    {
        // Define el mensaje que se enviará en la notificación
        $mensaje = 'Se ha publicado una nueva convocatoria.';
        // Usa la fachada Notification para enviar el mensaje a todos los usuarios
        Notification::send($usuarios, new NotificacionGeneral($mensaje));
    }
    // Método estático que notifica a un usuario cuando cambia el estado de su postulación

    public static function cambioEstadoPostulacion($usuario, $estado)
    {
        // Crea un mensaje personalizado con el nuevo estado
        $mensaje = "Tu postulación ha cambiado de estado a: $estado.";
        // Usa el método notify del modelo User para enviar la notificación a ese usuario
        $usuario->notify(new NotificacionGeneral($mensaje));
    }
    // Método estático que notifica a un administrador cuando un aspirante se postula a una convocatoria

    public static function nuevaPostulacion($admin)
    {
        // Mensaje de notificación para el administrador
        $mensaje = 'Un aspirante se ha postulado a una convocatoria.';
        // Envia la notificación al administrador
        $admin->notify(new NotificacionGeneral($mensaje));
    }
    // Método estático que notifica a un usuario cuando ha sido contratado

    public static function nuevaContratacion($usuario)
    {
        // Mensaje de felicitación por contratación
        $mensaje = 'Has sido contratado. ¡Felicitaciones!';
        $usuario->notify(new NotificacionGeneral($mensaje));
    }
}
