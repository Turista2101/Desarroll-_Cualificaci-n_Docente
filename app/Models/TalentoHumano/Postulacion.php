<?php

namespace App\Models\TalentoHumano;

use App\Models\Usuario\User;
use App\Models\TalentoHumano\Convocatoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Postulacion extends Model
{
    protected $table = 'postulacions';
    protected $primaryKey = 'id_postulacion';

    protected $fillable = [
        'user_id',
        'convocatoria_id',
        'estado_postulacion',
        
    ];

    public function usuarioPostulacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function convocatoriaPostulacion(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class, 'convocatoria_id', 'id_convocatoria');
    }
}
