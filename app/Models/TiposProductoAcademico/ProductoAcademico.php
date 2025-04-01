<?php

namespace App\Models\TiposProductoAcademico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TiposProductoAcademico\AmbitoDivulgacion;

class ProductoAcademico extends Model
{
    use HasFactory;
    
    protected $table = 'producto_academicos';

    protected $primaryKey = 'id_producto_academico';

    protected $fillable = [
       
        'nombre_producto_academico',
    ];

    public function ambitoDivulgacionsProductoAcademico(): HasMany
    {
        return $this->hasMany(AmbitoDivulgacion::class,'producto_academico_id','id_producto_academico');
    }

  
}
