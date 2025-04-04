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

    public $resetLink;
    public $user;

    public function __construct($user, $resetLink)
    {
        $this->user = $user;
        $this->resetLink = $resetLink;
    }

    public function build()
    {
        return $this->subject('Restablecimiento de Contraseña')
                    ->html("
                        <p>Hola, {$this->user->name}</p>
                        <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                        <p><a href='{$this->resetLink}'>Haz clic aquí para restablecer tu contraseña</a></p>
                        <p>Si no solicitaste este cambio, ignora este mensaje.</p>
                        <p>Gracias,<br>" . config('app.name') . "</p>
                    ");
    }
}
