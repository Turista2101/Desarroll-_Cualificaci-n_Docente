<?php

namespace App\Constants\ConstAgregarEstudio;

class Graduado
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