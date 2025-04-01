<?php

namespace App\Constants\ConstAgregarEstudio;

class Graduado
{
    const SI = 'SI';
    const NO = 'NO';

    public static function all(): array
    {
        return [
            self::SI,
            self::NO,
        ];
    }
}