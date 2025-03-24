<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuario\User;

class Eps extends Model
{
    protected $table = 'eps';

    protected $fillable = [
        'eps_user_id',
        'nombre_eps',
        'tipo_afiliacion',
        'estado_afiliacion',
        'fecha_afiliacion_efectiva',
        'fecha_finalizacion_afiliacion',
        'tipo_afiliado',
        'numero_afiliado'
    ];

    // Relación uno a uno con la tabla users
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class,'eps_user_id');
    }

}
