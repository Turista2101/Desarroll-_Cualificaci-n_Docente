<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Aspirante\Estudio;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class EstudiosUsuarioExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
    {
        return Estudio::with([
            'usuarioEstudio'
        ])
            ->whereHas(
                'usuarioEstudio.roles',
                function ($query) {
                    $query->where('name', 'Aspirante');
                }
            )
            ->get()
            ->map(function ($estudio) {
                return [
                    'nombre' => $estudio->usuarioEstudio->primer_nombre . '' . $estudio->usuarioEstudio->segundo_nombre . '' . $estudio->usuarioEstudio->primer_apellido . '' . $estudio->usuarioEstudio->segundo_apellido,
                    'Email' => $estudio->usuarioEstudio->email,
                    'tipo_estudio' => $estudio->tipo_estudio,
                    'Grauado' => $estudio->graduado,
                    'institucion' => $estudio->institucion,
                    'fecha_graduacion' => $estudio->fecha_graduacion,
                    'titulo_convalidado' => $estudio->titulo_convalidado,
                    'fecha_convalidacion' => $estudio->fecha_convalidacion,
                    'resolucion_convalidacion' => $estudio->resolucion_convalidacion,
                    'posible_fecha_graduacion' => $estudio->posible_fecha_graduacion,
                    'titulo_estudio' => $estudio->titulo_estudio,
                    'fecha_inicio' => $estudio->fecha_inicio,
                    'fecha_fin' => $estudio->fecha_fin,
                ];
            });
    }
    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'Tipo de Estudio',
            'Graduado',
            'Institucion',
            'Fecha de Graduacion',
            'Titulo Convalidado',
            'Fecha de Convalidacion',
            'Resolucion de Convalidacion',
            'Posible Fecha de Graduacion',
            'Titulo de Estudio',
            'Fecha de Inicio',
            'Fecha de Fin'
        ];
    }

    public function title(): string
    {
        return 'Estudios';
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
