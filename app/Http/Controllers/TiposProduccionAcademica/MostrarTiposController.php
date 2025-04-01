<?php

namespace App\Http\Controllers\TiposProduccionAcademica;

use App\Models\TiposProductoAcademico\AmbitoDivulgacion;
use App\Models\TiposProductoAcademico\ProductoAcademico;

class MostrarTiposController
{

    
  public function obtenerProductosAcademicos()
   {
   return response()->json(ProductoAcademico::all(), 200);
   }

   // Método para obtener los tipos de productos académicos
  public function obtenerAmbitoDivulgacion()
    {
        return response()->json(AmbitoDivulgacion::all(), 200);
    }


}
  