<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuario\User;

class Rut extends Model
{
    protected $table = 'ruts';

    protected $fillable = [
        'rut_user_id',
        'nombre_rut',
        'razon_social',
        'tipo_persona',
        'codigo_ciiu',
        'Responsabilidades_tributarias',
    ];


    // Relación uno a uno con la tabla users
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rut_user_id');
    }


}
