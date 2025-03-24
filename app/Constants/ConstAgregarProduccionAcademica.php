<?php

namespace App\Constants;

// Tipos de constantes de produccion, contiene las diferentes tipos de producciones
// si desea agregar uno nuevo hagalo desde aqui, y se reflejara en la base de datos

class Produccion
{
    // Tipos de producciones
    public const ENSAYO_O_ARTICULO = 'Ensayo o artículo';
    public const VIDEO_CINEMATOGRAFICO_O_FONOGRAFICO = 'Video cinematográfico o fonográfico';
    public const LIBRO = 'Libro';
    public const PATENTE_DE_INVENCION = 'Patente de invención';
    public const ESTUDIOS_POS_DOCTORALES = 'Estudios posdoctorales';
    public const RESENA_CRITICA = 'Reseña Crítica';
    public const TRADUCCION = 'Traducción';
    public const OBRA_DE_CREACION_ARTISTICA = 'Obra de creación artística';
    public const PREMIO = 'Premio';
    public const ARTICULO_CORTO = 'Artículo corto';
    public const PRODUCCION_TECNICA = 'Producción técnica';
    public const PRODUCCION_SOFTWARE = 'Producción de software';
    public const EVALUACION_COMO_PAR = 'Evaluación como par';
    public const REVISION_DE_TEMA = 'Revisión de tema';
    public const REPORTES_DE_CASO = 'Reportes de caso';
    public const CARTA_AL_EDITOR = 'Carta al editor';
    public const DISTINCION_DE_TRABAJO_DE_GRADO = 'Distinción de trabajo de grado';
    public const PONENCIA_O_PLENARIA = 'Ponencia o plenaria';
    public const CAPITULO_DE_LIBRO = 'Capítulo de libro';
    public const ARREGLO_MUSICAL = 'Arreglo musical';
    public const COMUNICACION_Y_PROCESOS_ORGANIZACIONALES = 'Comunicación y procesos organizacionales';
    public const CONCIERTO = 'Concierto';
    public const CREACION_O_COMPOSICION_MUSICAL = 'Creación o composición musical';
    public const CURADURIA_ARTISTICA = 'Curaduría artística';
    public const INVESTIGACION_CREACION_COMUNICACION_SOCIAL = 'Investigación-creación en comunicación social';
    public const OBRA_DE_CREACION_EN_DISENO_O_COMUNICACION_VISUAL = 'Obra de creación en diseño o comunicación visual';
    public const RECITAL = 'Recital';
    public const RESIDENCIA_ARTISTICA = 'Residencia artística';


    // Retorna todos los tipos de producciones
    public static function all(): array
    {
        return [
            self::ENSAYO_O_ARTICULO,
            self::VIDEO_CINEMATOGRAFICO_O_FONOGRAFICO,
            self::LIBRO,
            self::PATENTE_DE_INVENCION,
            self::ESTUDIOS_POS_DOCTORALES,
            self::RESENA_CRITICA,
            self::TRADUCCION,
            self::OBRA_DE_CREACION_ARTISTICA,
            self::PREMIO,
            self::ARTICULO_CORTO,
            self::PRODUCCION_TECNICA,
            self::PRODUCCION_SOFTWARE,
            self::EVALUACION_COMO_PAR,
            self::REVISION_DE_TEMA,
            self::REPORTES_DE_CASO,
            self::CARTA_AL_EDITOR,
            self::DISTINCION_DE_TRABAJO_DE_GRADO,
            self::PONENCIA_O_PLENARIA,
            self::CAPITULO_DE_LIBRO,
            self::ARREGLO_MUSICAL,
            self::COMUNICACION_Y_PROCESOS_ORGANIZACIONALES,
            self::CONCIERTO,
            self::CREACION_O_COMPOSICION_MUSICAL,
            self::CURADURIA_ARTISTICA,
            self::INVESTIGACION_CREACION_COMUNICACION_SOCIAL,
            self::OBRA_DE_CREACION_EN_DISENO_O_COMUNICACION_VISUAL,
            self::RECITAL,
            self::RESIDENCIA_ARTISTICA
        ];
    }
}
