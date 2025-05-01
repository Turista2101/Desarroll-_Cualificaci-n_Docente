<?php

namespace App\Http\Controllers\TiposProduccionAcademica;

use App\Models\TiposProductoAcademico\AmbitoDivulgacion;
use App\Models\TiposProductoAcademico\ProductoAcademico;

class MostrarTiposController
{

    
  //metodo para obtener productos academicos
  public function obtenerProductosAcademicos()
   {
   return response()->json(ProductoAcademico::all(), 200);
   }

   // Método para obtener los tipos de productos académicos
  public function obtenerAmbitoDivulgacion()
    {
        return response()->json(AmbitoDivulgacion::all(), 200);
    }
  
    //obtener ambito de divulgacion por producto academico
  public function obtenerAmbitoDivulgacionPorProductoAcademico($id_producto_academico)
    {
        $ambitos = AmbitoDivulgacion::where('producto_academico_id', $id_producto_academico)->get();
        return response()->json($ambitos, 200);
    }


}
  