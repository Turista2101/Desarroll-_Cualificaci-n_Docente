<?php

namespace App\Http\Requests\RequestDocente\RequestEvaluacionDocente;

use App\Constants\ConstDocente\EstadoEvaluacionDocente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarEvaluacionDocenteRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'promedio_evaluacion_docente' => 'sometimes|required|numeric|min:0|max:10',
            'estado_evaluacion_docente' => 'sometimes|nullable|in:' . implode(',', EstadoEvaluacionDocente::all()),
        ];
    }

    /**
     * Manejo de errores de validación.
     */
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