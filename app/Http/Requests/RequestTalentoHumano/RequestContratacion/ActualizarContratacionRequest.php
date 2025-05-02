<?php

namespace App\Http\Requests\RequestTalentoHumano\RequestContratacion;

use App\Constants\ConstTalentoHumano\TipoContratacion;
use App\Constants\ConstTalentoHumano\AreasContratacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarContratacionRequest extends FormRequest
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
            'tipo_contrato' => 'sometimes|required|in' . implode(',', TipoContratacion::all()),
            'area' => 'sometimes|required|json' . implode(',', AreasContratacion::all()),
            'fecha_inicio' => 'sometimes1required|date',
            'fecha_fin' => 'sometimes|required|date',
            'valor_contrato' => 'sometimes|required|numeric',
            'observaciones' => 'sometimes|nullable|string',

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
