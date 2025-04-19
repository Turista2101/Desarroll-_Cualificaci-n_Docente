<?php

namespace App\Http\Requests\RequestTalentoHumano\RequestConvocatoria;

use App\Constants\ConstTalentoHumano\EstadoConvocatoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrearConvocatoriaRequest extends FormRequest
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
            'nombre_convocatoria'   => 'required|string|max:255',
            'tipo'                  => 'required|string|max:255',
            'fecha_publicacion'     => 'required|date',
            'fecha_cierre'          => 'required|date|after:fecha_publicacion',
            'descripcion'           => 'required|string|max:1000',
            'estado_convocatoria'   => 'required|in:' . implode(',', EstadoConvocatoria::all()),
            'archivo'               => 'required|file|mimes:pdf,jpg,png|max:2048',

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
