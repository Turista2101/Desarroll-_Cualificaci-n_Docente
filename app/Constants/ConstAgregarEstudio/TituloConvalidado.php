<?php

namespace App\Constants\ConstAgregarEstudio;


class TituloConvalidado
{
    const SI = 'Si';
    const NO = 'No';

    public static function all(): array
    {
        return [
            self::SI,
            self::NO,
        ];
    }
}