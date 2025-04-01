<?php

namespace App\Constants\ConstRut;

class TipoPersona
{
    const NATURAL = 'Natural';
    const JURIDICA = 'Juridica';

    public static function all(): array
    {
        return [
            self::NATURAL,
            self::JURIDICA,
        ];
    }

}
