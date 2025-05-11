<?php

namespace App\Exports;

use App\Models\Aspirante\ProduccionAcademica;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class ProduccionesAcademicasUsuarioExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function collection()
    {
        return ProduccionAcademica::with([
            'usuarioProduccionAcademica',
        ])
            ->whereHas(
                'usuarioProduccionAcademica.roles',
                function ($query) {
                    $query->where('name', 'Aspirante');
                }
            )
            ->get()
            ->map(
                function ($produccionAcademica) {
                    return [
                        'docente' => $produccionAcademica->usuarioProduccionAcademica->primer_nombre . ' ' . $produccionAcademica->usuarioProduccionAcademica->segundo_nombre . ' ' . $produccionAcademica->usuarioProduccionAcademica->primer_apellido . ' ' . $produccionAcademica->usuarioProduccionAcademica->segundo_apellido,
                        'email' => $produccionAcademica->usuarioProduccionAcademica->email,
                        'ambito_divulgacion' => $produccionAcademica->ambito_divulgacion,
                        'titulo' => $produccionAcademica->titulo,
                        'numero_autores' => $produccionAcademica->numero_autores,
                        'medio_divulgacion' => $produccionAcademica->medio_divulgacion,
                        'fecha_divulgacion' => $produccionAcademica->fecha_divulgacion,

                    ];
                }
            );
    }
    public function headings(): array
    {
        return [
            'Docente',
            'Email',
            'Ámbito de divulgación',
            'Título',
            'Número de autores',
            'Medio de divulgación',
            'Fecha de divulgación',
        ];
    }

    public function title(): string
    {
        return 'Producciones académicas';
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
