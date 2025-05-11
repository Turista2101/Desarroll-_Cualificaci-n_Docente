<?php

namespace App\Exports;

use App\Models\Aspirante\Idioma;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class IdiomasUsuarioExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Idioma::with([
            'usuarioidioma'
        ])
            ->whereHas(
                'usuarioidioma.roles',
                function ($query) {
                    $query->where('name', 'Aspirante');
                }
            )
            ->get()
            ->map(function ($idioma) {
                return [
                    'docente' => $idioma->usuarioidioma->primer_nombre . ' ' . $idioma->usuarioidioma->segundo_nombre . ' ' . $idioma->usuarioidioma->primer_apellido . ' ' . $idioma->usuarioidioma->segundo_apellido,
                    'email' => $idioma->usuarioidioma->email,
                    'idioma' => $idioma->idioma,
                    'institucion_idioma' => $idioma->institucion_idioma,
                    'fecha_certificado' => $idioma->fecha_certificado,
                    'nivel' => $idioma->nivel,
                ];
            });
    }
    public function headings(): array
    {
        return [
            'Docente',
            'Email',
            'Idioma',
            'Institución Idioma',
            'Fecha Certificado',
            'Nivel',
        ];
    }

    public function title(): string
    {
        return 'Idiomas';
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
