<?php

namespace App\Constants\ConstAgregarIdioma;


class NivelIdioma
{
        public const  A1 = 'A1';
        public const  A2 = 'A2';
        public const  B1 = 'B1';
        public const  B2 = 'B2';
        public const  C1 = 'C1';
        public const  C2 = 'C2';
        
        public static function all(): array
        {
            return [
                self::A1,
                self::A2,
                self::B1,
                self::B2,
                self::C1,
                self::C2,
            ];
        }
}