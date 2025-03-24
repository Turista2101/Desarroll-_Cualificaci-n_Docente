<?php

namespace App\Constants;

// Tipos de constantes de eps, contiene los diferentes tipos de seleccion que usamos en eps
// si desea agregar uno nuevo hagalo desde aqui, y se reflejara en la base de datos

class TipoAfiliacion
{
    // Tipos de afiliacion
    public const CONTRIBUTIVO = 'Contributivo';
    public const SUBSIDIADO = 'Subsidiado';
    public const VINCULADO = 'Vinculado';
    public const ESPECIAL = 'Especial';
    public const EXCEPCION = 'Excepción';
    // Retorna todos los tipos de afiliacion
    public static function all(): array
    {
        return [
            self::CONTRIBUTIVO,
            self::SUBSIDIADO,
            self::VINCULADO,
            self::ESPECIAL,
            self::EXCEPCION
        ];
    }
}

class EstadoAfiliacion
{
    // Estados de afiliacion
    public const ACTIVO = 'Activo';
    public const INACTIVO = 'Inactivo';
    public const SUSPENDIDO = 'Suspendido';
    // Retorna todos los estados de afiliacion
    public static function all(): array
    {
        return [
            self::ACTIVO,
            self::INACTIVO,
            self::SUSPENDIDO
        ];
    }
}

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