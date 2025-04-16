<?php

namespace App\Http\Requests\RequestAspirante\RequestEstudio;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\ConstAgregarEstudio\Graduado;
use App\Constants\ConstAgregarEstudio\TiposEstudio;
use App\Constants\ConstAgregarEstudio\TituloConvalidado;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CrearEstudioRequest extends FormRequest
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
            'tipo_estudio'              => 'required|in:' . implode(',', TiposEstudio::all()),
            'graduado'                  => 'required|in:' . implode(',', Graduado::all()),
            'institucion'               => 'required|string|min:7|max:100',
            'fecha_graduacion'          => 'nullable|date',
            'titulo_convalidado'        => 'required|in:' . implode(',', TituloConvalidado::all()),
            'fecha_convalidacion'       => 'nullable|date',
            'resolucion_convalidacion'  => 'nullable|string|min:7|max:100',
            'posible_fecha_graduacion'  => 'nullable|date',
            'titulo_estudio'            => 'nullable|string|min:7|max:100',
            'fecha_inicio'              => 'required|date',
            'fecha_fin'                 => 'nullable|date',
            'archivo'                   => 'required|file|mimes:pdf,jpg,png|max:2048', // Validación del archivo
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
