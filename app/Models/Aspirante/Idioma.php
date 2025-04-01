<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuario\User;

class Idioma extends Model
{
    // definimos el nombre de la tabla
    protected $table = 'idiomas';
    // definimos la clave primaria de la tabla|
    protected $primaryKey = 'id_idioma';

    protected $fillable = [
        'user_id',
        'idioma',
        'institucion_idioma',
        'fecha_certificado',
        'nivel'
    ];



}
