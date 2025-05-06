<?php

namespace App\Http\Requests\RequestAspirante\RequestExperiencia;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\ConstAgregarExperiencia\TiposExperiencia;
use App\Constants\ConstAgregarExperiencia\TrabajoActual;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;



class CrearExperienciaRequest extends FormRequest
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
            'tipo_experiencia'             => 'required|string|in:' . implode(',', TiposExperiencia::all()),
            'institucion_experiencia'      => 'required|string|min:3|max:100|regex:/^[\pL\pN\s\-]+$/u',
            'cargo'                        => 'required|string|min:3|max:100|regex:/^[\pL\pN\s\-]+$/u',
            'trabajo_actual'               => 'required|in:' . implode(',', TrabajoActual::all()),
            'intensidad_horaria'           => 'nullable|integer|min:1|max:168',
            'fecha_inicio'                 => 'required|date', // volver este campo a requerido
            'fecha_finalizacion'           => 'nullable|date|after_or_equal:fecha_inicio',
            'fecha_expedicion_certificado' => 'nullable|date',
            'archivo'                      => 'required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
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
