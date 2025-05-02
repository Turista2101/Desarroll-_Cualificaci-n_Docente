<?php

namespace App\Http\Requests\RequestDocente\RequestEvaluacionDocente;


use App\Constants\ConstDocente\EstadoEvaluacionDocente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrearEvaluacionDocenteRequest extends FormRequest
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
            'promedio_evaluacion_docente' => 'required|numeric|min:0|max:10',
            'estado_evaluacion_docente' => 'nullable|in:' . implode(',', EstadoEvaluacionDocente::all()),
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