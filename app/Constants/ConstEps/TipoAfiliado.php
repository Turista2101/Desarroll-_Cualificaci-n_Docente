<?php

namespace App\Constants\ConstEps;

class TipoAfiliado
{
    // Tipos de afiliados
    public const COTIZANTE = 'Cotizante';
    public const BENEFICIARIO = 'Beneficiario';
    public const CABEZA_FAMILIA = 'Cabeza de familia';
    public const AFILIADO_ADICIONAL_O_ADHERENTE = 'Afiliado adicional o adherente';
    public const PENSIONADO = 'Pensionado';
    public const DESEMPLEADO = 'Desempleado';
    public const DESPLAZADO = 'Desplazado';
    public const AFILIADO_POR_CONVENIO_INTERNACIONAL = 'Afiliado por convenio internacional';
    // Retorna todos los tipos de afiliados
    public static function all(): array
    {
        return [
            self::COTIZANTE,
            self::BENEFICIARIO,
            self::CABEZA_FAMILIA,
            self::AFILIADO_ADICIONAL_O_ADHERENTE,
            self::PENSIONADO,
            self::DESEMPLEADO,
            self::DESPLAZADO,
            self::AFILIADO_POR_CONVENIO_INTERNACIONAL
        ];
    }

}