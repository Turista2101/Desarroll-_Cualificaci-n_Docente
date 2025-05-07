<?php

namespace App\Http\Requests\RequestNormativa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarNormativaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    // Método que determina si el usuario está autorizado para realizar esta solicitud.
    {
        return true;
    // Retorna `true`, lo que significa que cualquier usuario está autorizado para usar esta solicitud.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    // Método que define las reglas de validación para los datos enviados en la solicitud.
    {
        return [
            'nombre' => 'sometimes|required|string|max:255',
              // El campo `nombre` es opcional (`sometimes`), pero si está presente, es obligatorio (`required`).
            // Debe ser una cadena (`string`) con un máximo de 255 caracteres.
            'descripcion' => 'nullable|string',
            // El campo `descripcion` es opcional (`nullable`), pero si está presente, debe ser una cadena (`string`).
            'tipo' => 'sometimes|required|string|max:50',
             // El campo `tipo` es opcional, pero si está presente, es obligatorio.
            // Debe ser una cadena con un máximo de 50 caracteres.
            'archivo' => 'sometimes|nullable|file|mimes:pdf|max:2048', // Validación del archivo
             // El campo `archivo` es opcional, pero si está presente, debe ser un archivo (`file`) con extensiones permitidas
            // (`pdf`, `doc`, `docx`) y su tamaño no debe exceder los 2048 KB.
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    // Método que se ejecuta cuando la validación falla.
    {
        throw new HttpResponseException(
        // Lanza una excepción `HttpResponseException` para devolver una respuesta JSON personalizada.
            response()->json([
                'success' => false,
                // Indica que la solicitud no fue exitosa.
                'message' => 'Error en el formulario',
                // Mensaje general de error.
                'errors' => $validator->errors(),
                // Incluye los errores específicos de validación generados por el validador.
            ], 422)
                // Devuelve un código de estado HTTP 422 (Unprocessable Entity) para indicar errores de validación.
        );
    }
}