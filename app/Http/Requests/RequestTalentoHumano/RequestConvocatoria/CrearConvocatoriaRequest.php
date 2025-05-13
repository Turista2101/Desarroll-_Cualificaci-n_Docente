<?php

namespace App\Http\Requests\RequestTalentoHumano\RequestConvocatoria;

use App\Constants\ConstTalentoHumano\EstadoConvocatoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class CrearConvocatoriaRequest extends FormRequest
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
            'nombre_convocatoria'   => 'required|string|max:255|regex:/^[\pL\pN\s\-]+$/u',
            // El campo `nombre_convocatoria` es obligatorio (`required`).
            // Debe ser una cadena (`string`) con un máximo de 255 caracteres y cumplir con un patrón regex que permite letras,
            // números, espacios y guiones.
            'tipo'                  => 'required|string|max:255|regex:/^[\pL\pN\s\-]+$/u',
             // El campo `tipo` es obligatorio (`required`).
            // Debe ser una cadena con un máximo de 255 caracteres y cumplir con el mismo patrón regex.
            'fecha_publicacion'     => 'required|date',
            // El campo `fecha_publicacion` es obligatorio (`required`) y debe ser una fecha válida (`date`).
            'fecha_cierre'          => 'required|date|after:fecha_publicacion',
             // El campo `fecha_cierre` es obligatorio (`required`).
            // Debe ser una fecha válida (`date`) y posterior a `fecha_publicacion`.
            'descripcion'           => 'required|string|max:1000|regex:/^[\pL\pN\s\-]+$/u',
             // El campo `descripcion` es obligatorio (`required`).
            // Debe ser una cadena con un máximo de 1000 caracteres y cumplir con el mismo patrón regex.
            'estado_convocatoria'   => ['required','string', Rule::in(EstadoConvocatoria::all())],
              // El campo `estado_convocatoria` es obligatorio (`required`).
            // Su valor debe estar dentro de los valores definidos en `EstadoConvocatoria::all()`.
            'archivo'               => 'required|file|mimes:pdf|max:2048',
             // El campo `archivo` es obligatorio (`required`).
            // Debe ser un archivo (`file`) con extensiones permitidas (`pdf`, `jpg`, `png`) y su tamaño no debe exceder los 2048 KB.
        ];
    }
    protected function failedValidation(Validator $validator)
        // Método que se ejecuta cuando la validación falla.
    {
        throw new HttpResponseException(
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
