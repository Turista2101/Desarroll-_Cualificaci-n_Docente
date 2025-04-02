<?php

namespace App\Models\Aspirante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Aspirante\Documento;


class Experiencia extends Model
{
    // Definimos el nombre de la tabla
    protected $table = 'experiencias';
    // Definimos la clave primaria de la tabla
    protected $primaryKey = 'id_experiencia';

    protected $fillable = [
        'tipo_experiencia',
        'institucion_experiencia',
        'cargo',
        'trabajo_actual',
        'intensidad_horaria',
        'fecha_inicio',
        'fecha_finalizacion',
        'fecha_expedicion_certificado',
    ];

    // Relación polimórfica con documentos
    public function documentosExperiencia():MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }




   
}
