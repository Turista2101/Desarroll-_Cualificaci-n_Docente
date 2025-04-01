<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;
use App\Models\TiposProductoAcademico\AmbitoDivulgacion;

class ProduccionAcademica extends Model
{
    protected $table = 'produccion_academicas';

    protected $primaryKey = 'id_produccion_academica';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'titulo',
        'numero_autores',
        'medio_divulgacion',
        'fecha_divulgacion',
    ];


    // Relación uno a muchos con el modelo AmbitoDivulgacion
    public function ambitoDivulgacionProduccionAcademica()
    {
        return $this->belongsTo(AmbitoDivulgacion::class, 'ambito_divulgacion_id', 'id_ambito_divulgacion');
    }


}
