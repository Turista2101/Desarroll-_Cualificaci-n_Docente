<?php

namespace App\Http\Controllers\TalentoHumano;


use App\Http\Requests\RequestTalentoHumano\RequestConvocatoria\ActualizarConvocatoriaRequest;
use App\Http\Requests\RequestTalentoHumano\RequestConvocatoria\CrearConvocatoriaRequest;
use App\Models\TalentoHumano\Convocatoria;
use Illuminate\Support\Facades\DB;
use App\Services\ArchivoService;


class ConvocatoriaController
{
    protected $archivoService;

    /**
     * Constructor del controlador de convocatorias.
     *
     * Inyecta el servicio `ArchivoService`, utilizado para gestionar operaciones de almacenamiento,
     * actualización y eliminación de archivos relacionados con las convocatorias.
     *
     * @param ArchivoService $archivoService Servicio responsable de la gestión de archivos asociados a las convocatorias.
     */
    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    /**
     * Crear una nueva convocatoria.
     *
     * Este método permite registrar una nueva convocatoria en el sistema.
     * La operación se ejecuta dentro de una transacción para garantizar la integridad de los datos.
     * Si se adjunta un archivo (como los términos o reglamentos de la convocatoria), este se almacena
     * mediante el servicio `ArchivoService` y se asocia al registro de la convocatoria.
     * En caso de producirse un error durante la operación, se captura la excepción y se retorna
     * una respuesta con el mensaje correspondiente.
     *
     * @param CrearConvocatoriaRequest $request Solicitud validada con los datos de la convocatoria y archivo opcional.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con mensaje de éxito o mensaje de error.
     */
    public function crearConvocatoria(CrearConvocatoriaRequest $request)
    {
        try {
            DB::transaction(function () use ($request) { // Inicio de la transacción

                $datosConvocatoria = $request->validated(); // Validamos los datos de la solicitud
                $convocatoria = Convocatoria::create($datosConvocatoria); // Creamos la convocatoria en la base de datos

                if ($request->hasFile('archivo')) { // Verificamos si se ha subido un archivo

                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $convocatoria, 'Convocatorias'); // Guardamos el archivo asociado a la convocatoria
                }
            });

            return response()->json([ // Retornamos una respuesta JSON
                'mensaje' => 'Convocatoria creada exitosamente',
            ], 201);
        } catch (\Exception $e) { // manejamos cualquier excepción que ocurra durante la transacción
            return response()->json([
                'mensaje' => 'Error al crear la convocatoria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una convocatoria existente.
     *
     * Este método permite modificar los datos de una convocatoria previamente registrada, identificada por su ID.
     * La operación se realiza dentro de una transacción para asegurar la integridad de los datos. 
     * Si se adjunta un nuevo archivo (como una versión actualizada del documento de convocatoria), 
     * este se reemplaza utilizando el servicio `ArchivoService`.
     * En caso de que la convocatoria no exista o se produzca un error durante la operación, 
     * se captura la excepción y se retorna una respuesta con el mensaje de error correspondiente.
     *
     * @param ActualizarConvocatoriaRequest $request Solicitud validada con los nuevos datos de la convocatoria y archivo opcional.
     * @param int $id ID de la convocatoria que se desea actualizar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con mensaje de éxito o mensaje de error.
     */
    public function actualizarConvocatoria(ActualizarConvocatoriaRequest $request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) { // Inicio de la transacción
                $convocatoria = Convocatoria::findOrFail($id); // Buscamos la convocatoria por su ID
                $convocatoria->update($request->validated()); // Actualizamos la convocatoria con los datos validados

                if ($request->hasFile('archivo')) { // Verificamos si se ha subido un archivo
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $convocatoria, 'Convocatorias'); // Actualizamos el archivo asociado a la convocatoria
                }
            });

            return response()->json([ // Retornamos una respuesta JSON
                'mensaje' => 'Convocatoria actualizada exitosamente',
            ], 200);
        } catch (\Exception $e) { // manejamos cualquier excepción que
            return response()->json([
                'mensaje' => 'Error al actualizar la convocatoria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una convocatoria existente.
     *
     * Este método permite eliminar una convocatoria del sistema, identificada por su ID. 
     * Antes de eliminar el registro, se eliminan los archivos asociados utilizando el servicio `ArchivoService`.
     * Toda la operación se realiza dentro de una transacción para asegurar la consistencia de los datos.
     * En caso de que la convocatoria no exista o se produzca un error durante la eliminación,
     * se captura una excepción y se retorna una respuesta adecuada.
     *
     * @param int $id ID de la convocatoria que se desea eliminar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con mensaje de éxito o mensaje de error.
     */
    public function eliminarConvocatoria($id)
    {
        try {
            $convocatoria = Convocatoria::findOrFail($id); // Buscamos la convocatoria por su ID
            DB::transaction(function () use ($convocatoria) { // Inicio de la transacción
                $this->archivoService->eliminarArchivoDocumento($convocatoria); // Eliminamos el archivo asociado a la convocatoria
                $convocatoria->delete(); // Eliminamos la convocatoria de la base de datos
            });

            return response()->json(['mensaje' => 'Convocatoria eliminada exitosamente'], 200); // Retornamos una respuesta JSON indicando que la convocatoria fue eliminada exitosamente

        } catch (\Exception $e) { // manejamos cualquier excepción que ocurra durante la transacción
            return response()->json([
                'mensaje' => 'Error al eliminar la convocatoria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las convocatorias registradas con sus documentos asociados.
     *
     * Este método recupera todas las convocatorias disponibles en el sistema, incluyendo los documentos
     * relacionados mediante la relación `documentosConvocatoria`. Las convocatorias se ordenan por su
     * fecha de creación en orden descendente. Para cada documento, se genera la URL pública del archivo 
     * utilizando el helper `asset()`. Si no se encuentran convocatorias, se lanza una excepción con código 404.
     * En caso de error, se captura la excepción y se retorna una respuesta adecuada.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la lista de convocatorias o mensaje de error.
     */
    public function obtenerConvocatorias()
    {
        try {
            $convocatorias = Convocatoria::with('documentosConvocatoria') // Obtenemos todas las convocatorias con sus documentos asociados
                ->orderBy('created_at', 'desc') // Ordenamos las convocatorias por fecha de creación de forma descendente
                ->get();

            if ($convocatorias->isEmpty()) { // Verificamos si no se encontraron convocatorias
                throw new \Exception('No se encontraron convocatorias', 404);
            }
            foreach ($convocatorias as $convocatoria) { // Recorremos cada convocatoria
                foreach ($convocatoria->documentosConvocatoria as $documento) { // Recorremos cada documento asociado a la convocatoria
                    $documento->archivo_url = asset('storage/' . $documento->archivo); // Asignamos la URL del archivo usando el helper asset
                }
            }
            return response()->json(['convocatorias' => $convocatorias], 200); // Retornamos una respuesta JSON con las convocatorias

        } catch (\Exception $e) {
            return response()->json([ // Si ocurre algún error, capturamos la excepción y devolvemos un mensaje de error
                'mensaje' => 'Error al obtener las convocatorias',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Obtener una convocatoria específica por su ID.
     *
     * Este método busca una convocatoria mediante su ID y carga los documentos asociados a ella
     * utilizando la relación `documentosConvocatoria`. Para cada documento que tenga un archivo,
     * se genera una URL pública utilizando el helper `asset()`. Si la convocatoria no existe,
     * se lanza una excepción y se retorna una respuesta con el mensaje correspondiente.
     *
     * @param int $id ID de la convocatoria que se desea consultar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la información de la convocatoria o mensaje de error.
     */
    public function obtenerConvocatoriaPorId($id)
    {
        try {
            $convocatoria = Convocatoria::with('documentosConvocatoria')->findOrFail($id); // Buscamos la convocatoria por su ID y cargamos los documentos asociados

            foreach ($convocatoria->documentosConvocatoria as $documento) { // Recorremos cada documento asociado a la convocatoria
                if (!empty($documento->archivo)) { // Verificamos si el campo archivo no está vacío
                    $documento->archivo_url = asset('storage/' . $documento->archivo); // Asignamos la URL del archivo usando el helper asset
                }
            }
            return response()->json(['convocatoria' => $convocatoria], 200); // Retornamos una respuesta JSON con la convocatoria encontrada

        } catch (\Exception $e) {
            return response()->json([ // Si ocurre algún error, capturamos la excepción y devolvemos un mensaje de error
                'mensaje' => 'Error al obtener la convocatoria',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
