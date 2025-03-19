<?php

namespace App\Models;
 
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 
use Spatie\Permission\Traits\HasRoles;
 
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;
 
    /**
     * The attributes that are mass assignable.
     *
     * @var array&lt;int, string>
     */
    protected $fillable = [

        'tipo_identificacion',
        'numero_identificacion',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'email',
        'password',
    ];
 
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array&lt;int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
 
    /**
     * Get the attributes that should be cast.
     *
     * @return array&lt;string, string>
     */
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
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

   

}