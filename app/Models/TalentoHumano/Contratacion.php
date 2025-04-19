<?php

namespace App\Models\TalentoHumano;

use App\Models\Usuario\User;
use Illuminate\Database\Eloquent\Model;

class Contratacion extends Model
{
    protected $table = 'contratacions';

    protected $primaryKey = 'id_contratacion';

    protected $fillable = [
        'user_id',
        'tipo_contrato',
        'area',
        'fecha_inicio',
        'fecha_fin',
        'valor_contrato',
        'observaciones'
    ];

    public function usuarioContratacion()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }



}
