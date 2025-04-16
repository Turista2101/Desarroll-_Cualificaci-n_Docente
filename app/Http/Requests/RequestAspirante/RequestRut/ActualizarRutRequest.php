<?php

namespace App\Http\Requests\RequestAspirante\RequestRut;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\ConstRut\CodigoCiiu;
use App\Constants\ConstRut\TipoPersona;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarRutRequest extends FormRequest
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
            'numero_rut'                    => 'sometimes|required|string|min:7|max:100',
            'razon_social'                  => 'sometimes|required|string|min:7|max:100',
            'tipo_persona'                  => 'sometimes|required|in:' . implode(',', TipoPersona::all()),
            'codigo_ciiu'                   => 'sometimes|required|in:' . implode(',', CodigoCiiu::all()),
            'responsabilidades_tributarias' => 'sometimes|required|string|min:7|max:100',
            'archivo'                       => 'sometimes|required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
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
