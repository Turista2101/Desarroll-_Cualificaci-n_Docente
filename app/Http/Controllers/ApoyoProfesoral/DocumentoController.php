<?php

namespace App\Http\Controllers\ApoyoProfesoral;
use App\Models\Aspirante\Documento;
use App\Models\Aspirante\Estudio;

class DocumentoController
{


    // esto es secretaria aun no se hace
    public function filtrarPorTipoEstudio($tipo)
{
    // Obtener los IDs de los estudios que coincidan con el tipo solicitado
    $estudios = Estudio::where('tipo_estudio', $tipo)->pluck('id');

    // Obtener documentos relacionados con esos estudios
    $documentos = Documento::whereIn('documentable_id', $estudios)
        ->where('documentable_type', Estudio::class)
        ->get();

    return response()->json(['documentos' => $documentos]);
}
}
