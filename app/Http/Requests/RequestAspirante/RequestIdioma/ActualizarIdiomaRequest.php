<?php

namespace App\Http\Requests\RequestAspirante\RequestIdioma;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\ConstAgregarIdioma\NivelIdioma;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class ActualizarIdiomaRequest extends FormRequest
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
            'idioma'             => 'sometimes|required|string|max:255',
            'institucion_idioma' => 'sometimes|required|string|max:255',
            'fecha_certificado'  => 'sometimes|nullable|date',//poner este campo otra ves a requerido
            'nivel'              => 'sometimes|required|in:' . implode(',', NivelIdioma::all()),
            'archivo'            => 'sometimes|required|file|mimes:pdf,jpg,png|max:2048', // Validación de archivo
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
