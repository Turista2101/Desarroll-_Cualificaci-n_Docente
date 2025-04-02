<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Aspirante\Documento;


class Idioma extends Model
{
    // definimos el nombre de la tabla
    protected $table = 'idiomas';
    // definimos la clave primaria de la tabla|
    protected $primaryKey = 'id_idioma';

    protected $fillable = [
        'idioma',
        'institucion_idioma',
        'fecha_certificado',
        'nivel'
    ];
    
       // Relación polimórfica con documentos
       public function documentosIdioma():MorphMany
       {
           return $this->morphMany(Documento::class, 'documentable');
       }



}
