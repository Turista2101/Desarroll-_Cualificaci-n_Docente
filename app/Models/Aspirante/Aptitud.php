<?php

namespace App\Models\Aspirante;

use App\Models\Usuario\User;
use Illuminate\Database\Eloquent\Model;

class Aptitud extends Model
{
    protected $table = 'aptitudes';
    protected $primaryKey = 'id_aptitud';

    protected $fillable = [
        'user_id',
        'nombre_aptitud',
        'descripcion_aptitud',
    ];

    public function usuarioAptitud()
    {
        return $this->belongsToMany(User::class, 'user_id','id');
    }

}
