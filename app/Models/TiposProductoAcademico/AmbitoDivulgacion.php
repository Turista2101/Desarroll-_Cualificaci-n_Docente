<?php

namespace App\Models\TiposProductoAcademico;

use App\Models\Aspirante\ProduccionAcademica;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TiposProductoAcademico\ProductoAcademico;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmbitoDivulgacion extends Model
{
    use HasFactory;

    protected $table = 'ambito_divulgacions';

    protected $primaryKey = 'id_ambito_divulgacion';

    protected $fillable = [

        'nombre_ambito_divulgacion',
        'producto_academico_id',
        
    ];

    //relacion uno a muchos inversa con producto academico
    public function productoAcademicoAmbitoDivulgacion():BelongsTo
    {
        return $this->belongsTo(ProductoAcademico::class, 'producto_academico_id', 'id_producto_academico');
    }

    //relacion uno a muchos con produccion academica
    public function produccionAcademicasAmbitoDivulgacion():HasMany
    {
        return $this->hasMany(ProduccionAcademica::class, 'ambito_divulgacion_id', 'id_ambito_divulgacion');
    }
}
