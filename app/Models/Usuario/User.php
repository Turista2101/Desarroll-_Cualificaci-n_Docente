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
use App\Models\Aspirante\Idioma;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Aspirante\Experiencia;
use App\Models\Aspirante\Documento;
use App\Models\Aspirante\Estudio;
use App\Models\Aspirante\ProduccionAcademica;

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
        'municipio_id',
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
    public function informacionContactoUsuario():HasOne
    {
        return $this->hasOne(InformacionContacto::class, 'user_id','id');
    }

    // relacion de uno a uno con la tabla municipios
    public function municipioUsuarios():BelongsTo
    {
        return $this->belongsTo(Municipio::class,'municipio_id', 'id_municipio');
    }
    
    // relacion de uno a uno con la tabla eps
    public function epsUsuario():HasOne
    {
        return $this->hasOne(Eps::class, 'user_id', 'id');
    }

    // relacion de uno a uno con la tabla ruts
    public function rutUsuario():HasOne
    {
        return $this->hasOne(Rut::class, 'user_id', 'id');
    }

    // relacion de uno a uno con la tabla documentos
    public function documentosUsuario(): HasMany
    {
        return $this->hasMany(Documento::class, 'user_id', 'id');
    }

   

   
   

    
    
}