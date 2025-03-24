<?php

namespace App\Models\Usuario;

// creamos un modelo llamado User para mediar entre la tabla users y la base de datos
// en este modelo se definen las relaciones con otras tablas
// en este caso la tabla users tiene una relacion de uno a uno con la tabla informacion_contacto

 
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Aspirante\InformacionContacto;
use App\Models\Ubicacion\Municipio;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Aspirante\Eps;
use App\Models\Aspirante\Rut;

class User extends Authenticatable implements JWTSubject
{
    // utilizamos el trait HasFactory para definir la fabrica de usuarios
    // utilizamos el trait Notifiable para definir las notificaciones de usuarios
    // utilizamos el trait HasRoles para definir los roles de usuarios
    use HasFactory, Notifiable, HasRoles;
    protected $table = 'users';

 
    /**
     * The attributes that are mass assignable.
     *
     * @var array&lt;int, string>
     */
    // definimos los campos de la tabla users que se pueden llenar
    protected $fillable = [

        'user_municipio_id',
        'tipo_identificacion',
        'numero_identificacion',
        'genero',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'fecha_nacimiento',
        'estado_civil',
        'email',
        'password'
    ];
 
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array&lt;int, string>
     */
    // definimos los campos de la tabla users que no se pueden mostrar
    protected $hidden = [
        'password',
        'remember_token',
    ];
 
    /**
     * Get the attributes that should be cast.
     *
     * @return array&lt;string, string>
     */
    // definimos los campos de la tabla users que se deben convertir a un tipo de dato especifico
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
 
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    // definimos el identificador que se almacenara en la reclamacion del sujeto del JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    // devolver un array de valores clave, que contenga cualquier reclamacion personalizada que se agregara al JWT
    public function getJWTCustomClaims()
    {
        return [];
    }


    // relacion de uno a uno con la tabla informacion_contacto
    public function informacionContacto():HasOne
    {
        return $this->hasOne(InformacionContacto::class, 'informacioncontacto_user_id');
    }

    // relacion de uno a uno con la tabla municipios
    public function municipio():BelongsTo
    {
        return $this->belongsTo(Municipio::class,'user_municipio_id');
    }
    
    // relacion de uno a uno con la tabla eps
    public function eps():HasOne
    {
        return $this->hasOne(Eps::class, 'eps_user_id');
    }

    // relacion de uno a uno con la tabla ruts
    public function rut():HasOne
    {
        return $this->hasOne(Rut::class, 'rut_user_id');
    }
   

}