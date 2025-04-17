<?php

namespace App\Models\Aspirante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Aspirante\Documento;

/**
 * @OA\Schema(
 *     schema="Experiencia",
 *     type="object",
 *     title="Experiencia",
 *     description="Esquema de una experiencia laboral",
 *     @OA\Property(property="id_experiencia", type="integer", example=1, description="ID de la experiencia"),
 *     @OA\Property(property="tipo_experiencia", type="string", example="Docencia universitaria", description="Tipo de experiencia"),
 *     @OA\Property(property="institucion_experiencia", type="string", example="Universidad Nacional", description="Nombre de la institución"),
 *     @OA\Property(property="cargo", type="string", example="Profesor Asociado", description="Cargo desempeñado"),
 *     @OA\Property(property="trabajo_actual", type="string", enum={"Si", "No"}, example="Si", description="Indica si es el trabajo actual"),
 *     @OA\Property(property="intensidad_horaria", type="integer", example=40, description="Intensidad horaria semanal"),
 *     @OA\Property(property="fecha_inicio", type="string", format="date", example="2020-01-01", description="Fecha de inicio de la experiencia"),
 *     @OA\Property(property="fecha_finalizacion", type="string", format="date", nullable=true, example="2023-01-01", description="Fecha de finalización de la experiencia"),
 *     @OA\Property(property="fecha_expedicion_certificado", type="string", format="date", nullable=true, example="2023-02-01", description="Fecha de expedición del certificado"),
 *     @OA\Property(property="documentosExperiencia", type="array", description="Documentos asociados a la experiencia",
 *         @OA\Items(
 *             @OA\Property(property="id_documento", type="integer", example=1),
 *             @OA\Property(property="archivo", type="string", example="documentos/Experiencias/archivo.pdf"),
 *             @OA\Property(property="archivo_url", type="string", example="http://localhost/storage/documentos/Experiencias/archivo.pdf"),
 *             @OA\Property(property="user_id", type="integer", example=1)
 *         )
 *     )
 * )
 */

class Experiencia extends Model
{
    // Definimos el nombre de la tabla
    protected $table = 'experiencias';
    // Definimos la clave primaria de la tabla
    protected $primaryKey = 'id_experiencia';

    protected $fillable = [
        'tipo_experiencia',
        'institucion_experiencia',
        'cargo',
        'trabajo_actual',
        'intensidad_horaria',
        'fecha_inicio',
        'fecha_finalizacion',
        'fecha_expedicion_certificado',
    ];

    // Relación polimórfica con documentos
    public function documentosExperiencia():MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }
}
