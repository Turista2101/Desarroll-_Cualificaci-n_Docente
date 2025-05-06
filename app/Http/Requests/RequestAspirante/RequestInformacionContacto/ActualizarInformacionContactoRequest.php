<?php

namespace App\Http\Requests\RequestAspirante\RequestInformacionContacto;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\ConstInformacionContacto\CategoriaLibretaMilitar;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarInformacionContactoRequest extends FormRequest
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
            'municipio_id'                          => 'sometimes|required|exists:municipios,id_municipio',
            'categoria_libreta_militar'             => 'sometimes|nullable|in:' . implode(',', CategoriaLibretaMilitar::all()),//llamo a la constante categoria libreta militar para obtener los tipos de libreta militar
            'numero_libreta_militar'                => 'sometimes|nullable|string|max:50|regex:/^[\pL\pN\s\-]+$/u',
            'numero_distrito_militar'               => 'sometimes|nullable|string|max:50|regex:/^[\pL\pN\s\-]+$/u',
            'direccion_residencia'                  => 'sometimes|nullable|string|max:100|regex:/^[\pL\pN\s\-]+$/u',
            'barrio'                                => 'sometimes|nullable|string|max:100|regex:/^[\pL\pN\s\-]+$/u',
            'telefono_movil'                        => 'sometimes|required|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'celular_alternativo'                   => 'sometimes|nullable|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'correo_alterno'                        => 'sometimes|nullable|string|email|max:100|unique:users,email',
            'archivo'                               => 'sometimes|nullable|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
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
