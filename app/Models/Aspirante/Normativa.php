<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;

class Normativa extends Model
{
    protected $table = 'normativa';
    protected $primaryKey = 'id_normativa';
    public $timestamps = false;

    protected $fillable = [
        'id_normativa',
        'nombre',
        'descripcion',
        'tipo',
    ];

}
