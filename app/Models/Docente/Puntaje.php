<?php

namespace App\Models\Docente;

use App\Models\Usuario\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Puntaje extends Model
{
    protected $table = 'puntajes';
    protected $primaryKey = 'id_puntaje';

    protected $fillable = [
        'user_id',
        'puntaje_total',
        'estado_puntaje',
    ];


    public function usuarioPuntaje(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
