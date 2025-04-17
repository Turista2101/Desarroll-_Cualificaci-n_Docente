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

/**
 * @OA\Schema(
 *     schema="Usuario",
 *     type="object",
 *     title="Usuario",
 *     description="Esquema de un usuario",
 *     @OA\Property(property="id", type="integer", example=1, description="ID del usuario"),
 *     @OA\Property(property="municipio_id", type="integer", example=1, description="ID del municipio asociado al usuario"),
 *     @OA\Property(property="tipo_identificacion", type="string", enum={"Cédula de ciudadanía", "Cédula de extranjería", "Número único de identificación personal", "Pasaporte", "Registro civil", "Número por secretaría de educación", "Servicio nacional de pruebas", "Tarjeta de identidad", "Tarjeta profesional"}, example="Cédula de ciudadanía", description="Tipo de identificación del usuario"),
 *     @OA\Property(property="numero_identificacion", type="string", example="123456789", description="Número de identificación del usuario"),
 *     @OA\Property(property="genero", type="string", enum={"Masculino", "Femenino", "Otro"}, nullable=true, example="Masculino", description="Género del usuario"),
 *     @OA\Property(property="primer_nombre", type="string", example="Juan", description="Primer nombre del usuario"),
 *     @OA\Property(property="segundo_nombre", type="string", nullable=true, example="Carlos", description="Segundo nombre del usuario"),
 *     @OA\Property(property="primer_apellido", type="string", example="Pérez", description="Primer apellido del usuario"),
 *     @OA\Property(property="segundo_apellido", type="string", nullable=true, example="Gómez", description="Segundo apellido del usuario"),
 *     @OA\Property(property="fecha_nacimiento", type="string", format="date", example="1990-01-01", description="Fecha de nacimiento del usuario"),
 *     @OA\Property(property="estado_civil", type="string", enum={"Soltero", "Casado", "Divorciado", "Viudo"}, nullable=true, example="Soltero", description="Estado civil del usuario"),
 *     @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com", description="Correo electrónico del usuario"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2023-01-01T12:00:00Z", description="Fecha de verificación del correo electrónico"),
 *     @OA\Property(property="password", type="string", format="password", example="password123", description="Contraseña del usuario"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z", description="Fecha de creación del usuario"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T12:00:00Z", description="Fecha de última actualización del usuario")
 * )
 */
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

      //relacion polimorfica con la tabla documentos
      public function documentosUser()
      {
          return $this->morphMany(Documento::class, 'documentable');
      }

   

   
   

    
    
}