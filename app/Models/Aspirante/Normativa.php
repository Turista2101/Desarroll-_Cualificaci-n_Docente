<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Normativa extends Model
{
    protected $table = 'normativas';
    protected $primaryKey = 'id_normativa';
    public $timestamps = false;

    protected $fillable = [
        'id_normativa',
        'nombre',
        'descripcion',
        'tipo',
    ];

    public function documentosNormativa():MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }
    

}
