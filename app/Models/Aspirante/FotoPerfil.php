<?php

namespace App\Models\Aspirante;

use App\Models\Aspirante\Documento;
use App\Models\Usuario\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class FotoPerfil extends Model
{
    protected $table = 'foto_perfils';
    protected $primaryKey = 'id_foto_perfil';

    protected $fillable = [
        'user_id',
    ];


    public function usuarioFotoPerfil()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function documentosFotoPerfil():MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

  
}
