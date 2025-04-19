<?php

namespace App\Models\TalentoHumano;

use App\Models\Aspirante\Documento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Convocatoria extends Model
{
    protected $table = 'convocatorias';
    protected $primaryKey = 'id_convocatoria';

    protected $fillable = [
        'nombre_convocatoria',
        'tipo',
        'fecha_publicacion',
        'fecha_cierre',
        'descripcion',
        'estado_convocatoria'
    ];

    //relacion polimorfica con la tabla documentos
    public function documentosConvocatoria():MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    //relacion uno a muchos con la tabla postulaciones
    public function postulacionesConvocatoria()
    {
        return $this->hasMany(Postulacion::class, 'convocatoria_id', 'id_convocatoria');
    }






}
