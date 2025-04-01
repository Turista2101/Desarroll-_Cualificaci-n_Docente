<?php

namespace App\Models\Aspirante;

use App\Models\Usuario\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    // definimos el nombre de la tabla
    protected $table = 'documentos';
    // definimos la clave primaria de la tabla
    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'archivo',
        'estado',
        'tipo_documento',
        'documentable',
    ];

    // Relación uno a uno con la tabla users
    public function usuarioDocumento():BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
