<?php

namespace App\Services;

use App\Models\Usuario\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CalculoPuntajeDocenteService
{
    const CATEGORIAS = [
        'Asistente' => [
            'formacion' => 'Maestría',
            'ingles' => 'B1',
            'evaluacion' => 4.0,
            'puntaje' => 20,
            'años' => 4
        ],
        'Asociado' => [
            'formacion' => 'Doctorado',
            'ingles' => 'B2',
            'evaluacion' => 4.0,
            'puntaje' => 30,
            'años' => 6
        ],
        'Titular' => [
            'formacion' => 'Doctorado',
            'ingles' => 'B2',
            'evaluacion' => 4.0,
            'puntaje' => 60,
            'años' => 8
        ],
    ];

    public function evaluar(User $user): array
    {
        $contrato = $user->contratacionUsuario;

        if (!$contrato || strtolower(trim($contrato->tipo_contrato)) !== 'planta') {
            return [
                'valido' => false,
                'categoria_lograda' => 'Ninguna',
                'razon' => 'Solo aplica para docentes de planta.',
                'puntaje_total' => 0,
                'faltantes_por_categoria' => [],
            ];
        }

        $anios = $this->calcularAniosPlanta($user);
        $puntaje = $this->calcularPuntaje($user);

        $faltantesPorCategoria = [];

        // Verificar si tiene doctorado aprobado
        $tieneDoctorado = $user->estudiosUsuario->contains(
            fn($e) =>
            $e->documentosEstudio->contains('estado', 'aprobado') &&
                $e->tipo_estudio === 'Doctorado'
        );

        // Evaluar si puede ser Titular (debe cumplir todos los requisitos)
        $titular = self::CATEGORIAS['Titular'];
        $formacionOk = $tieneDoctorado;
        $inglesOk = $user->idiomasUsuario->contains(
            fn($i) =>
            $i->documentosIdioma->contains('estado', 'aprobado') &&
                $i->nivel === $titular['ingles']
        );
        $evaluacionOk = optional($user->evaluacionDocenteUsuario)->promedio_evaluacion_docente >= $titular['evaluacion'];
        $puntajeOk = $puntaje >= $titular['puntaje'];
        $aniosOk = $anios >= $titular['años'];

        $cumpleTitular = [
            'años' => $aniosOk,
            'formacion' => $formacionOk,
            'ingles' => $inglesOk,
            'evaluacion' => $evaluacionOk,
            'puntaje' => $puntajeOk,
        ];

        if (collect($cumpleTitular)->every(fn($v) => $v)) {
            return [
                'valido' => true,
                'categoria_lograda' => 'Titular',
                'razon' => 'Cumple todos los requisitos para Titular.',
                'puntaje_total' => $puntaje,
                'faltantes_por_categoria' => [],
            ];
        }

        // Si tiene doctorado, asignarlo como Asociado directamente
        if ($tieneDoctorado) {
            $faltantes = collect($cumpleTitular)->filter(fn($v) => !$v)->keys()->toArray();
            return [
                'valido' => true,
                'categoria_lograda' => 'Asociado',
                'razon' => 'Tiene Doctorado aprobado. Clasificado como Asociado. Para ascender a Titular le faltan: ' . implode(', ', $faltantes),
                'puntaje_total' => $puntaje,
                'faltantes_por_categoria' => ['Titular' => $faltantes],
            ];
        }

        // Si no tiene doctorado, evaluar Asistente
        $asistente = self::CATEGORIAS['Asistente'];
        $formacionOk = $user->estudiosUsuario->contains(
            fn($e) =>
            $e->documentosEstudio->contains('estado', 'aprobado') &&
                $e->tipo_estudio === $asistente['formacion']
        );
        $inglesOk = $user->idiomasUsuario->contains(
            fn($i) =>
            $i->documentosIdioma->contains('estado', 'aprobado') &&
                $i->nivel === $asistente['ingles']
        );
        $evaluacionOk = optional($user->evaluacionDocenteUsuario)->promedio_evaluacion_docente >= $asistente['evaluacion'];
        $puntajeOk = $puntaje >= $asistente['puntaje'];
        $aniosOk = $anios >= $asistente['años'];

        $cumpleAsistente = [
            'años' => $aniosOk,
            'formacion' => $formacionOk,
            'ingles' => $inglesOk,
            'evaluacion' => $evaluacionOk,
            'puntaje' => $puntajeOk,
        ];

        if (collect($cumpleAsistente)->every(fn($v) => $v)) {
            return [
                'valido' => true,
                'categoria_lograda' => 'Asistente',
                'razon' => 'Cumple todos los requisitos para Asistente.',
                'puntaje_total' => $puntaje,
                'faltantes_por_categoria' => [],
            ];
        }

        $faltantesAsistente = collect($cumpleAsistente)->filter(fn($v) => !$v)->keys()->toArray();

        return [
            'valido' => true,
            'categoria_lograda' => 'Auxiliar',
            'razon' => 'No cumple requisitos para categorías superiores. Le faltan para Asistente: ' . implode(', ', $faltantesAsistente),
            'puntaje_total' => $puntaje,
            'faltantes_por_categoria' => ['Asistente' => $faltantesAsistente],
        ];
    }

    public function calcularPuntaje(User $user): int
    {
        $total = 0;

        foreach ($user->produccionAcademicaUsuario as $produccion) {
            $documentosAprobados = $produccion->documentosProduccionAcademica
                ->where('estado', 'aprobado');

            if ($documentosAprobados->isNotEmpty()) {
                $ambitoId = $produccion->ambito_divulgacion_id;

                if ($ambitoId !== null) {
                    $clasificacion = $this->clasificacionPorAmbito($ambitoId);

                    $total += match ($clasificacion) {
                        'top' => 10,
                        'a' => 6,
                        'b' => 3,
                        default => 0,
                    };
                }
            }
        }

        foreach ($user->experienciasUsuario as $exp) {
            if (
                $exp->documentosExperiencia->contains('estado', 'aprobado') &&
                strtoupper(trim($exp->institucion_experiencia)) === 'UNIVERSIDAD UNIAUTONOMA DEL CAUCA'
            ) {
                $total += Carbon::parse($exp->fecha_inicio)
                    ->diffInYears(Carbon::parse($exp->fecha_fin));
            }
        }

        return $total;
    }

    protected function calcularAniosPlanta(User $user): int
    {
        $contrato = $user->contratacionUsuario;

        if (!$contrato || strtolower(trim($contrato->tipo_contrato)) !== 'planta') {
            return 0;
        }

        $inicio = Carbon::parse($contrato->fecha_inicio);
        $fin = $contrato->fecha_fin ? Carbon::parse($contrato->fecha_fin) : now();

        return $inicio->diffInYears($fin);
    }

    protected function clasificacionPorAmbito(int $ambitoId): string
    {
        return match ($ambitoId) {
            1, 20, 21, 25, 27, 29, 34, 46, 50, 54, 62, 65 => 'top',
            2, 12, 15, 26, 28, 30, 35, 38, 47, 51, 55, 63, 66, 73, 45 => 'a',
            3, 13, 16, 19, 31, 36, 39, 48, 52, 56, 64, 41, 42 => 'b',
            default => 'ninguna'
        };
    }
}
