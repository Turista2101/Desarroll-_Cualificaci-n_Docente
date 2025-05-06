<?php

namespace App\Http\Requests\RequestTalentoHumano\RequestConvocatoria;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\ConstTalentoHumano\EstadoConvocatoria;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarConvocatoriaRequest extends FormRequest
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
            'nombre_convocatoria'   => 'sometimes|required|string|max:255|regex:/^[\pL\pN\s\-]+$/u',
            'tipo'                  => 'sometimes|required|string|max:255|regex:/^[\pL\pN\s\-]+$/u',
            'fecha_publicacion'     => 'sometimes|required|date',
            'fecha_cierre'          => 'sometimes|required|date|after:fecha_publicacion',
            'descripcion'           => 'sometimes|required|string|max:1000|regex:/^[\pL\pN\s\-]+$/u',
            'estado_convocatoria'   => 'sometimes|required|in:' . implode(',', EstadoConvocatoria::all()),
            'archivo'               => 'sometimes|nullable|file|mimes:pdf,jpg,png|max:2048',
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
