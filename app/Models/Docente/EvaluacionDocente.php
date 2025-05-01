<?php

namespace App\Models\Docente;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario\User;

class EvaluacionDocente extends Model
{
   protected $table = 'evaluacion_docentes';
   protected $primaryKey = 'id_evaluacion_docente';

   protected $fillable = [
       'user_id',
       'promedio_evaluacion_docente',
       'estado_evaluacion_docente',

   ];
   
   public function usuarioEvaluacionDocente()
   {
       return $this->belongsTo(User::class, 'user_id', 'id');
   }

}
