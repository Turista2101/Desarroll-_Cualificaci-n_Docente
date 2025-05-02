<?php

namespace App\Http\Controllers\TalentoHumano;// Declaramos el namespace donde se encuentra este controlador

// Importación de clases necesarias para el funcionamiento del controlador


use App\Http\Requests\RequestTalentoHumano\RequestConvocatoria\ActualizarConvocatoriaRequest;
use App\Http\Requests\RequestTalentoHumano\RequestConvocatoria\CrearConvocatoriaRequest;
use App\Models\Aspirante\Documento;
use App\Models\TalentoHumano\Convocatoria;
use Illuminate\Support\Facades\DB; // Usado para realizar transacciones de base de datos
use Illuminate\Support\Facades\Storage;  // Para manejar almacenamiento de archivos
use App\Notifications\NotificacionGeneral;  // Para enviar notificaciones
use Illuminate\Support\Facades\Notification;  // Facade de notificaciones
use App\Models\Usuario\User;  // Para interactuar con el modelo de usuario
use App\Services\ArchivoService;  // Servicio para gestionar los archivos adjuntos


class ConvocatoriaController
{
    protected $archivoService;// Declaramos una propiedad para el servicio de manejo de archivos

    // Constructor que inyecta el servicio de manejo de archivos

    public function __construct(ArchivoService $archivoService)// Guardamos el servicio en la propiedad
    {
        $this->archivoService = $archivoService;
    }

    // Método para crear una convocatoria
    public function crearConvocatoria(CrearConvocatoriaRequest $request)
    {
        try {
             // Inicia una transacción para asegurarse de que todo se ejecute correctamente
            $convocatoria = DB::transaction(function () use ($request) {
                // Validamos los datos del request
                $datosConvocatoria = $request->validated();
                 // Creamos el registro de convocatoria en la base de datos
                $convocatoria = Convocatoria::create($datosConvocatoria);
                // Si el request incluye un archivo, lo guardamos
                if ($request->hasFile('archivo')) {
                // Llamamos al servicio de archivos para guardar el archivo
                    $this->archivoService->guardarArchivoDocumento($request->file('archivo'), $convocatoria, 'Convocatorias');
                }

                return $convocatoria;// Devolvemos la convocatoria creada
            });
        // Retornamos una respuesta JSON con un mensaje de éxito
            return response()->json([
                'mensaje' => 'Convocatoria creada exitosamente',
                'data' => $convocatoria// Devolvemos los datos de la convocatoria creada
            ], 201); // Código de respuesta HTTP 201 (Creado)
        } catch (\Exception $e) {
        // Si ocurre algún error, capturamos la excepción y devolvemos un mensaje de error
            return response()->json([
                'mensaje' => 'Error al crear la convocatoria',
                'error' => $e->getMessage()
            ], 500);// Código de respuesta HTTP 500 (Error interno del servidor)
        }
    }

    // Actualizar un registro de convocatoria
    public function actualizarConvocatoria(ActualizarConvocatoriaRequest $request, $id)
    {
        try {
            // Inicia una transacción para asegurarse de que todo se ejecute correctamente
            $convocatoria = DB::transaction(function () use ($request, $id) {
                // Buscamos la convocatoria por su ID
                $convocatoria = Convocatoria::findOrFail($id);
                // Actualizamos los datos de la convocatoria
                $convocatoria->update($request->validated());
                // Si el request incluye un archivo, lo actualizamos
                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento($request->file('archivo'), $convocatoria, 'Convocatorias');
                }

                return $convocatoria;// Devolvemos la convocatoria actualizada
            });
 // Retornamos una respuesta JSON con un mensaje de éxito y los datos actualizados
            return response()->json([
                'mensaje' => 'Convocatoria actualizada exitosamente',
                'data' => $convocatoria->fresh()// Obtenemos la versión más reciente de la convocatoria
            ], 200);  // Código de respuesta HTTP 200 (OK)
        } catch (\Exception $e) {
             // Si ocurre algún error, capturamos la excepción y devolvemos un mensaje de error
            return response()->json([
                'mensaje' => 'Error al actualizar la convocatoria',
                'error' => $e->getMessage()
            ], 500);// Código de respuesta HTTP 500 (Error interno del servidor)
        }
    }

     // Método para eliminar una convocatoria
    public function eliminarConvocatoria($id)
    {
        try {
        // Buscamos la convocatoria por su ID
            $convocatoria = Convocatoria::findOrFail($id);
        // Iniciamos una transacción para eliminar de manera atómica
            DB::transaction(function () use ($convocatoria) {
            // Eliminamos el archivo asociado a la convocatoria
                $this->archivoService->eliminarArchivoDocumento($convocatoria);
            // Eliminamos la convocatoria de la base de datos
                $convocatoria->delete();
            });
            // Retornamos una respuesta JSON con un mensaje de éxito
            return response()->json(['mensaje' => 'Convocatoria eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            // Si ocurre algún error, capturamos la excepción y devolvemos un mensaje de error
            return response()->json([
                'mensaje' => 'Error al eliminar la convocatoria',
                'error' => $e->getMessage()
            ], 500);// Código de respuesta HTTP 500 (Error interno del servidor)
        }
    }

    // Método para obtener todas las convocatorias
    public function obtenerConvocatorias()
    {
        try {
            // Obtenemos todas las convocatorias, incluyendo sus documentos asociados
            $convocatorias = Convocatoria::with('documentosConvocatoria')->orderBy('created_at', 'desc')->get();
            // Si no se encuentran convocatorias, lanzamos una excepción
            if ($convocatorias->isEmpty()) {
                throw new \Exception('No se encontraron convocatorias', 404); // Excepción con código de error 404 (No encontrado)

            }
            // Recorremos las convocatorias y asignamos la URL del archivo a cada documento
            foreach ($convocatorias as $convocatoria) {
                foreach ($convocatoria->documentosConvocatoria as $documento) {
            // Asignamos la URL del archivo usando el helper asset
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }
            // Retornamos una respuesta JSON con las convocatorias
            return response()->json(['convocatorias' => $convocatorias], 200);
        } catch (\Exception $e) {
        // Si ocurre algún error, capturamos la excepción y devolvemos un mensaje de error
            return response()->json([
                'mensaje' => 'Error al obtener las convocatorias',
                'error' => $e->getMessage() // Devolvemos el mensaje del error
            ], $e->getCode() ?: 500);  // Usamos el código de error de la excepción o el 500 si no hay código
        }
    }

    // Método para obtener una convocatoria por su ID
    public function obtenerConvocatoriaPorId($id)
    {
        try {
            // Obtenemos la convocatoria por su ID, incluyendo sus documentos asociados
            $convocatoria = Convocatoria::with('documentosConvocatoria')->findOrFail($id);
            // Asignamos la URL del archivo a cada documento de la convocatoria
            foreach ($convocatoria->documentosConvocatoria as $documento) {
                if (!empty($documento->archivo)) {
                // Asignamos la URL del archivo usando el helper asset
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }
            // Retornamos una respuesta JSON con la convocatoria
            return response()->json(['convocatoria' => $convocatoria], 200);// Código de respuesta HTTP 200 (OK)
        } catch (\Exception $e) {
            // Si ocurre algún error, capturamos la excepción y devolvemos un mensaje de error
            return response()->json([
                'mensaje' => 'Error al obtener la convocatoria',
                'error' => $e->getMessage()// Devolvemos el mensaje del error
            ], $e->getCode() ?: 500);  // Usamos el código de error de la excepción o el 500 si no hay código
        }
    }
}
