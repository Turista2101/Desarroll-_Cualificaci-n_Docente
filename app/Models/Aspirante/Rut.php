<?php

namespace App\Models\Aspirante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuario\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Rut",
 *     type="object",
 *     title="Rut",
 *     description="Esquema de un registro de RUT",
 *     @OA\Property(property="id_rut", type="integer", example=1, description="ID del RUT"),
 *     @OA\Property(property="numero_rut", type="string", example="123456789", description="Número del RUT"),
 *     @OA\Property(property="razon_social", type="string", example="Empresa XYZ S.A.", description="Razón social de la empresa"),
 *     @OA\Property(property="tipo_persona", type="string", enum={"Natural", "Juridico"}, example="Juridico", description="Tipo de persona"),
 *     @OA\Property(property="codigo_ciiu", type="string", example="Industria manufacturera", description="Código CIIU asociado al RUT"),
 *     @OA\Property(property="responsabilidades_tributarias", type="string", example="IVA, Renta", description="Responsabilidades tributarias asociadas al RUT"),
 *     @OA\Property(property="documentosRut", type="array", description="Documentos asociados al RUT",
 *         @OA\Items(
 *             @OA\Property(property="id_documento", type="integer", example=1),
 *             @OA\Property(property="archivo", type="string", example="documentos/RUT/archivo.pdf"),
 *             @OA\Property(property="archivo_url", type="string", example="http://localhost/storage/documentos/RUT/archivo.pdf"),
 *             @OA\Property(property="user_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z", description="Fecha de creación del registro"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T12:00:00Z", description="Fecha de última actualización del registro")
 * )
 */
class Rut extends Model
{
    // creamos un modelo llamado Rut para mediar entre la tabla ruts y la base de datos
    use HasFactory;
    // definimos el nombre de la tabla
    protected $table = 'ruts';
    // definimos la clave primaria de la tabla
    protected $primaryKey = 'id_rut';

    protected $fillable = [
        'numero_rut',
        'razon_social',
        'tipo_persona',
        'codigo_ciiu',
        'responsabilidades_tributarias',
    ];
    
    
    //relacion polimorfica con la tabla documentos
    public function documentosRut()
    {
        return $this->morphMany(Documento::class, 'documentable');
    }
    

}
