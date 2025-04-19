<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotificacionGeneral extends Notification
{
    use Queueable;

    protected $mensaje;

    public function __construct($mensaje)
    {
        $this->mensaje = $mensaje;
    }

    public function via($notifiable)
    {
        return ['database']; // Solo base de datos
    }

    public function toDatabase($notifiable)
    {
        return [
            'mensaje' => $this->mensaje
        ];
    }
}
