<?php

namespace App\Constants;

class CodigoCiiu
{
    public const SECCION_A = 'Agricultura, ganadería, caza, silvicultura y pesca';
    public const SECCION_B = 'Explotación de minas y canteras';
    public const SECCION_C = 'Industria manufacturera';
    public const SECCION_D = 'Suministro de electricidad, gas, vapor y aire acondicionado';
    public const SECCION_E = 'Suministro de agua, alcantarillado, gestión de residuos y actividades de saneamiento';
    public const SECCION_F = 'Construcción';
    public const SECCION_G = 'Comercio al por mayor y al por menor; reparación de vehículos automotores y motocicletas';
    public const SECCION_H = 'Transporte y almacenamiento';
    public const SECCION_I = 'Alojamiento y servicios de comida';
    public const SECCION_J = 'Información y comunicaciones';
    public const SECCION_K = 'Actividades financieras y de seguros';
    public const SECCION_L = 'Actividades inmobiliarias';
    public const SECCION_M = 'Actividades profesionales, científicas y técnicas';
    public const SECCION_N = 'Actividades administrativas y de servicios auxiliares';
    public const SECCION_O = 'Administración pública y defensa; seguridad social obligatoria';
    public const SECCION_P = 'Educación';
    public const SECCION_Q = 'Actividades de salud humana y de asistencia social';
    public const SECCION_R = 'Artes, entretenimiento y recreación';
    public const SECCION_S = 'Otras actividades de servicios';
    public const SECCION_T = 'Actividades de los hogares como empleadores; actividades de los hogares como productores de bienes y servicios para uso propio';
    public const SECCION_U = 'Organizaciones y organismos extraterritoriales';


    public static function all(): array
    {
        return [
            self::SECCION_A,
            self::SECCION_B,
            self::SECCION_C,
            self::SECCION_D,
            self::SECCION_E,
            self::SECCION_F,
            self::SECCION_G,
            self::SECCION_H,
            self::SECCION_I,
            self::SECCION_J,
            self::SECCION_K,
            self::SECCION_L,
            self::SECCION_M,
            self::SECCION_N,
            self::SECCION_O,
            self::SECCION_P,
            self::SECCION_Q,
            self::SECCION_R,
            self::SECCION_S,
            self::SECCION_T,
            self::SECCION_U,
        ];
    }

    

}
