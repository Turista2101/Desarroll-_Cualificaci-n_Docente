<?php

namespace App\Http\Controllers\Constantes;

use App\Constants\ConstRut\CodigoCiiu;
use App\Constants\ConstUsuario\EstadoCivil;
use App\Constants\ConstUsuario\Genero;
use App\Constants\ConstUsuario\TipoIdentificacion;
use App\Constants\ConstInformacionContacto\CategoriaLibretaMilitar;
use App\Constants\ConstEps\EstadoAfiliacion;
use App\Constants\ConstEps\TipoAfiliacion;
use App\Constants\ConstEps\TipoAfiliado;
use App\Constants\ConstDocumentos\TipoDocumento;

class ConstantsController
{
    //añadimos los metodos para obtener los datos de los codigos ciiu
    public function getCodigosCiuu()
    {
        return response()->json(['codigos_ciiu' => CodigoCiiu::all()]);
    }

    //añadimos los metodos para obtener los datos de los usuarios
    public function getEstadocivil()
    {
        return response()->json(['estado_civil' => EstadoCivil::all()]);
    }

    public function getGenero()
    {
        return response()->json(['genero' => Genero::all()]);
    }

    public function getTipoIdentificacion()
    {
        return response()->json(['tipo_identificacion' => TipoIdentificacion::all()]);
    }
    
    //añadimos los metodos para obtener los datos de las categorias de libreta militar
    public function getCategoriaLibretaMilitar()
    {
        return response()->json(['categoria_libreta_militar' => CategoriaLibretaMilitar::all()]);
    }

    //añadimos los metodos para obtener los datos de las afiliaciones
    public function getEstadoAfilicacion()
    {
        return response()->json(['estado_afiliacion' => EstadoAfiliacion::all()]);
    }

    public function getTipoAfiliacion()
    {
        return response()->json(['tipo_afiliacion' => TipoAfiliacion::all()]);
    }

    public function getTipoAfiliado()
    {
        return response()->json(['tipo_afiliado' => TipoAfiliado::all()]);
    }

    //añadimos los metodos para obtener los datos de los tipos de documentos
    public function getTipoDocumento()
    {
        return response()->json(['tipo_documento' => TipoDocumento::all()]);
    }


}
