<?php

namespace App\Exports;

use App\Models\Usuario\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class UsuariosExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::with('roles')
            ->whereHas(
                'roles',
                function ($query) {
                    $query->where('name', 'Aspirante');
                }
            )
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'primer_nombre' => $user->primer_nombre,
                    'segundo_nombre' => $user->segundo_nombre,
                    'primer_apellido' => $user->primer_apellido,
                    'segundo_apellido' => $user->segundo_apellido,
                    'tipo_identificacion' => $user->tipo_identificacion,
                    'numero_identificacion' => $user->numero_identificacion,
                    'estado_civil' => $user->estado_civil,
                    'genero' => $user->genero,
                    'fecha_nacimiento' => $user->fecha_nacimiento,
                    'correo' => $user->email,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Primer Nombre',
            'Segundo Nombre',
            'Primer Apellido',
            'Segundo Apellido',
            'Tipo de Identificacion',
            'Numero de Identificacion',
            'Estado Civil',
            'Genero',
            'fecha Nacimiento',
            'Correo'
        ];
    }

    public function title(): string
    {
        return 'Usuarios';
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
