<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    // Usa los traits `Queueable` y `SerializesModels` para habilitar la encolación y la serialización de modelos.
    public $resetLink;
    // Propiedad pública para almacenar el enlace de restablecimiento de contraseña.
    public $user;
    // Propiedad pública para almacenar la información del usuario.

    public function __construct($user, $resetLink)
    // Constructor de la clase que recibe el usuario y el enlace de restablecimiento como parámetros.
    {
        $this->user = $user;
        // Asigna el usuario a la propiedad `$user`.
        $this->resetLink = $resetLink;
        // Asigna el enlace de restablecimiento a la propiedad `$resetLink`.
    }

    public function build()
        // Método que construye el correo electrónico.
    {
        return $this->subject('Restablecimiento de Contraseña')
            // Establece el asunto del correo como "Restablecimiento de Contraseña".
                    ->html("
                        <p>Hola, {$this->user->name}</p>
                        <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                        <p><a href='{$this->resetLink}'>Haz clic aquí para restablecer tu contraseña</a></p>
                        <p>Si no solicitaste este cambio, ignora este mensaje.</p>
                        <p>Gracias,<br>" . config('app.name') . "</p>
                    ");
    }
}
