<?php

namespace App\Models\Aspirante;

// creamos un modelo llamado InformacionContacto para mediar entre la tabla informacion_contactos y la base de datos
// en este modelo se definen las relaciones con otras tablas
// en este caso la tabla informacion_contactos tiene una relacion de muchos a uno con la tabla municipios
// y una relacion de muchos a uno con la tabla usuarios

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Ubicacion\Municipio;
use App\Models\Usuario\User;


class InformacionContacto extends Model
{
    use HasFactory;
    // definimos el nombre de la tabla
    protected $table = 'informacion_contactos';
    // definimos los campos de la tabla informacion_contactos que se pueden llenar
    protected $fillable = [
        'informacioncontacto_user_id',
        'informacioncontacto_municipio_id',
        'categoria_libreta_militar',
        'numero_libreta_militar',
        'numero_distrito_militar',
        'direccion_residencia',
        'barrio',
        'telefono_movil',
        'celular_alternativo',
        'correo_alterno',
        
    ];

    // relacion de muchos a uno con la tabla municipios
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'informacioncontacto_municipio_id');
    }



    // relacion de uno a uno con la tabla usuarios
    public function  usuario(): BelongsTo
    {
        return $this->belongsTo(User::class,'informacioncontacto_user_id');
    }






}
