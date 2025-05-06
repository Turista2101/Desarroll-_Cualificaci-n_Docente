<?php

namespace App\Http\Requests\RequestAspirante\RequestInformacionContacto;

use App\Constants\ConstInformacionContacto\CategoriaLibretaMilitar;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class CrearInformacionContactoRequest extends FormRequest
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
            'municipio_id'                          => 'required|exists:municipios,id_municipio',
            'categoria_libreta_militar'             => 'nullable|in:' . implode(',', CategoriaLibretaMilitar::all()),//llamo a la constante categoria libreta militar para obtener los tipos de libreta militar
            'numero_libreta_militar'                => 'nullable|string|max:50|regex:/^[\pL\pN\s\-]+$/u',
            'numero_distrito_militar'               => 'nullable|string|max:50|regex:/^[\pL\pN\s\-]+$/u',
            'direccion_residencia'                  => 'nullable|string|max:100|regex:/^[\pL\pN\s\-]+$/u',
            'barrio'                                => 'nullable|string|max:100|regex:/^[\pL\pN\s\-]+$/u',
            'telefono_movil'                        => 'required|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'celular_alternativo'                   => 'nullable|string|min:7|max:20|regex:/^[0-9+\-\s()]+$/',
            'correo_alterno'                        => 'nullable|string|email|max:100|unique:users,email',
            'archivo'                               => 'nullable|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
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
