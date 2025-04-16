<?php

namespace App\Http\Requests\RequestAspirante\RequestExperiencia;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\ConstAgregarExperiencia\TiposExperiencia;
use App\Constants\ConstAgregarExperiencia\TrabajoActual;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarExperienciaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'tipo_experiencia'             => 'sometimes|required|string|in:' . implode(',', TiposExperiencia::all()),
            'institucion_experiencia'      => 'sometimes|required|string|min:3|max:100',
            'cargo'                        => 'sometimes|required|string|min:3|max:100',
            'trabajo_actual'               => 'sometimes|required|in:' . implode(',', TrabajoActual::all()),
            'intensidad_horaria'           => 'sometimes|nullable|integer|min:1|max:168',
            'fecha_inicio'                 => 'sometimes|required|date', // volver este campo a requerido
            'fecha_finalizacion'           => 'sometimes|nullable|date|after_or_equal:fecha_inicio',
            'fecha_expedicion_certificado' => 'sometimes|nullable|date',
            'archivo'                      => 'sometimes|required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
           response()->json([
                'success' => false,
                'message' => 'Error en el formulario',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
