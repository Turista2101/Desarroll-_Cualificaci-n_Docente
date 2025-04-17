<?php

namespace App\Models\Aspirante;
use Illuminate\Database\Eloquent\Model;
use App\Models\Aspirante\Documento;

/**
 * @OA\Schema(
 *     schema="Estudio",
 *     type="object",
 *     title="Estudio",
 *     description="Esquema de un estudio",
 *     @OA\Property(property="id_estudio", type="integer", example=1, description="ID del estudio"),
 *     @OA\Property(property="tipo_estudio", type="string", example="Pregrado", description="Tipo de estudio"),
 *     @OA\Property(property="graduado", type="string", enum={"Si", "No"}, example="Si", description="Indica si el usuario se graduó"),
 *     @OA\Property(property="institucion", type="string", example="Universidad Nacional", description="Nombre de la institución"),
 *     @OA\Property(property="fecha_graduacion", type="string", format="date", nullable=true, example="2023-01-01", description="Fecha de graduación"),
 *     @OA\Property(property="titulo_convalidado", type="string", enum={"Si", "No"}, example="No", description="Indica si el título fue convalidado"),
 *     @OA\Property(property="fecha_convalidacion", type="string", format="date", nullable=true, example="2023-02-01", description="Fecha de convalidación"),
 *     @OA\Property(property="resolucion_convalidacion", type="string", nullable=true, example="12345-CONV", description="Resolución de convalidación"),
 *     @OA\Property(property="posible_fecha_graduacion", type="string", format="date", nullable=true, example="2024-12-01", description="Posible fecha de graduación"),
 *     @OA\Property(property="titulo_estudio", type="string", nullable=true, example="Ingeniería de Sistemas", description="Título del estudio"),
 *     @OA\Property(property="fecha_inicio", type="string", format="date", example="2018-01-01", description="Fecha de inicio del estudio"),
 *     @OA\Property(property="fecha_fin", type="string", format="date", nullable=true, example="2022-12-01", description="Fecha de finalización del estudio"),
 *     @OA\Property(property="documentosEstudio", type="array", description="Documentos asociados al estudio",
 *         @OA\Items(
 *             @OA\Property(property="id_documento", type="integer", example=1),
 *             @OA\Property(property="archivo", type="string", example="documentos/Estudios/archivo.pdf"),
 *             @OA\Property(property="archivo_url", type="string", example="http://localhost/storage/documentos/Estudios/archivo.pdf"),
 *             @OA\Property(property="user_id", type="integer", example=1)
 *         )
 *     )
 * )
 */

class Estudio extends Model
{
    // Definimos el nombre de la tabla
    protected $table = 'estudios';
    // Definimos la clave primaria de la tabla
    protected $primaryKey = 'id_estudio';

    protected $fillable = [
        'tipo_estudio',
        'graduado',
        'institucion',
        'fecha_graduacion',
        'titulo_convalidado',
        'fecha_convalidacion',
        'resolucion_convalidacion',
        'posible_fecha_graduacion',
        'titulo_estudio',
        'fecha_inicio',
        'fecha_fin'
    ];

     // Relación polimórfica con documentos
     public function documentosEstudio()
     {
         return $this->morphMany(Documento::class, 'documentable');
     }


}
