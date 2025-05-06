<?php

namespace App\Http\Requests\RequestAspirante\RequestRut;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\ConstRut\CodigoCiiu;
use App\Constants\ConstRut\TipoPersona;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrearRutRequest extends FormRequest
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
            'numero_rut'                    => 'required|string|min:7|max:100|regex:/^[\pL\pN\s\-]+$/u',
            'razon_social'                  => 'required|string|min:7|max:100|regex:/^[\pL\pN\s\-]+$/u',
            'tipo_persona'                  => 'required|in:' . implode(',', TipoPersona::all()),
            'codigo_ciiu'                   => 'required|in:' . implode(',', CodigoCiiu::all()),
            'responsabilidades_tributarias' => 'required|string|min:7|max:100',
            'archivo'                       => 'required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
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
