<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReporteCompletoExport;
use Maatwebsite\Excel\Facades\Excel; // Importa la fachada de Excel para manejar exportaciones

use Illuminate\Http\Request;

class ReporteController
{
    public function usuariosExcel(){
       try {
            // Genera el archivo Excel usando la clase ReporteCompletoExport
            return Excel::download(new ReporteCompletoExport, 'reporteUnidoc.xlsx');
            
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'message' => 'Error al generar el reporte.',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
