<?php

namespace App\Http\Requests\RequestAspirante\RequestProduccionAcademica;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarProduccionAcademicaRequest extends FormRequest
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
            'ambito_divulgacion_id' => 'sometimes|required|integer|exists:ambito_divulgacions,id_ambito_divulgacion',
            'titulo' => 'sometimes|required|string|max:255|regex:/^[\pL\pN\s\-]+$/u',
            'numero_autores' => 'sometimes|required|integer',
            'medio_divulgacion' => 'sometimes|required|string|max:255|regex:/^[\pL\pN\s\-]+$/u',
            'fecha_divulgacion' => 'sometimes|nullable|date',// volver este campo a requerido
            'archivo' => 'sometimes|nullable|file|mimes:pdf,doc,docx|max:2048',
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
