<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuario\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rut extends Model
{
    // creamos un modelo llamado Rut para mediar entre la tabla ruts y la base de datos
    use HasFactory;
    // definimos el nombre de la tabla
    protected $table = 'ruts';
    // definimos la clave primaria de la tabla
    protected $primaryKey = 'id_rut';

    protected $fillable = [
        'nombre_rut',
        'razon_social',
        'tipo_persona',
        'codigo_ciiu',
        'Responsabilidades_tributarias',
    ];
    
    
    //relacion polimorfica con la tabla documentos
    public function documentosRut()
    {
        return $this->morphMany(Documento::class, 'documentable');
    }
    

}
