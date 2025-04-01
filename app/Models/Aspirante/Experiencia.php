<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuario\User;

class Experiencia extends Model
{
    // Definimos el nombre de la tabla
    protected $table = 'experiencias_laborales';
    // Definimos la clave primaria de la tabla
    protected $primaryKey = 'id_experiencia';

    protected $fillable = [
        'user_id',
        'tipo_experiencia',
        'institucion_experiencia',
        'cargo',
        'trabajo_actual',
        'intensidad_horaria',
        'fecha_inicio',
        'fecha_finalizacion',
        'fecha_expedicion_certificado',
    ];




   
}
