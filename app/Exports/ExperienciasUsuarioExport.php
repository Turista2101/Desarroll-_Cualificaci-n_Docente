<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Aspirante\Experiencia;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExperienciasUsuarioExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
    {
        return Experiencia::with([
            'usuarioExperiencia'
        ])
            ->whereHas(
                'usuarioExperiencia.roles',
                function ($query) {
                    $query->where('name', 'Aspirante');
                }
            )
            ->get()
            ->map(function ($experiencia) {
                return [
                    'nombre' => $experiencia->usuarioExperiencia->primer_nombre . ' ' . $experiencia->usuarioExperiencia->segundo_nombre . ' ' . $experiencia->usuarioExperiencia->primer_apellido . ' ' . $experiencia->usuarioExperiencia->segundo_apellido,
                    'Email' => $experiencia->usuarioExperiencia->email,
                    'tipo_experiencia' => $experiencia->tipo_experiencia,
                    'institucion' => $experiencia->institucion_experiencia,
                    'cargo' => $experiencia->cargo,
                    'trabajo_actual' => $experiencia->trabajo_actual,
                    'intesidad_horaria' => $experiencia->intensidad_horaria,
                    'fecha_inicio' => $experiencia->fecha_inicio,
                    'fecha-finalizacion' => $experiencia->fecha_finalizacion,
                    'fecha_expedicion_certificado' => $experiencia->fecha_expedicion_certificado,
                ];
            });
    }
    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'Tipo de Experiencia',
            'Institucion',
            'Cargo',
            'Trabajo Actual',
            'Intensidad Horaria',
            'Fecha de Inicio',
            'Fecha de Finalizacion',
            'Fecha de Expedicion del Certificado'
        ];
    }
    public function title(): string
    {
        return 'Experiencias';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->freezePane('A2');
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Rango de la primera fila (A1 hasta la última columna)
        $lastColumn = $sheet->getHighestColumn(); // Ej: "M"
        $headerRange = "A1:{$lastColumn}1";

        // Aplica estilos
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'], // azul profesional
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }
}
