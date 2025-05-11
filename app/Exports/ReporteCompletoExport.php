<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteCompletoExport implements WithMultipleSheets
{
   
    public function sheets(): array
    {
        return [
            new UsuariosExport(),
            new EstudiosUsuarioExport(),
            new ProduccionesAcademicasUsuarioExport(),
            new IdiomasUsuarioExport(),
            new ExperienciasUsuarioExport(),

        ];
        
    }
}
