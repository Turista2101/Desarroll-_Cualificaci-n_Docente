<?php

namespace App\Http\Controllers\ApoyoProfesoral;

use App\Services\CertificadoDocenteService;
use App\Http\Requests\RequestSecretaria\CrearCertificadosMasivosRequest;
use App\Models\Usuario\User;
use App\Models\Aspirante\Estudio;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\ArchivoService;
use Illuminate\Http\File;



class GenerarCertificadosController
{

    protected $certificadoService;
    protected $archivoService;

    public function __construct(CertificadoDocenteService $certificadoService, ArchivoService $archivoService)
    {
        $this->certificadoService = $certificadoService;
        $this->archivoService = $archivoService;
    }

    public function listarDocentes()
    {
        try {
            $docentes = User::role('Docente')
                ->select(
                    'id',
                    DB::raw("CONCAT(primer_nombre, ' ', segundo_nombre, ' ', primer_apellido, ' ', segundo_apellido) AS nombre_completo"),
                    'email',
                    'numero_identificacion'
                )
                ->get();

            return response()->json([
                'data' => $docentes,
                'message' => $docentes->isEmpty() ? 'No hay docentes registrados.' : ''
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los docentes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function crearCertificadosMasivos(CrearCertificadosMasivosRequest $request)
    {
        foreach ($request->docentes as $docenteId) {
            // Verificar que el usuario tenga rol docente
            $docente = User::whereHas('roles', function ($q) {
                $q->where('name', 'Docente');
            })->find($docenteId);

            if (!$docente) {
                Log::warning("El usuario con ID $docenteId no tiene rol docente. Se omite.");
                continue;
            }

            // Generar el certificado PDF
            $pdfFile = $this->certificadoService->generarPDF([
                'docente_id'         => $docente->id,
                'nombre_docente'     => $docente->primer_nombre . ' ' . $docente->segundo_nombre . ' ' . $docente->primer_apellido . ' ' . $docente->segundo_apellido,
                'titulo_certificado' => $request->titulo_estudio,
                'fecha'              => Carbon::parse($request->fecha_fin)->translatedFormat('d \d\e F \d\e Y'),
            ]);

            // Crear estudio
            $estudio = Estudio::create([
                'user_id'                  => $docente->id,
                'tipo_estudio'             => $request->tipo_estudio,
                'graduado'                 => $request->graduado,
                'titulo_estudio'           => $request->titulo_estudio,
                'institucion'              => $request->institucion,
                'fecha_inicio'             => $request->fecha_inicio,
                'fecha_fin'                => $request->fecha_fin,
                'fecha_graduacion'         => $request->fecha_graduacion ?? $request->fecha_inicio,
                'titulo_convalidado'       => $request->titulo_convalidado,
                'fecha_convalidacion'      => $request->fecha_convalidacion,
                'resolucion_convalidacion' => $request->resolucion_convalidacion,
                'posible_fecha_graduacion' => $request->posible_fecha_graduacion,
            ]);

            // Guardar documento asociado (polimórfico)
            $this->archivoService->guardarArchivoDocumento($pdfFile, $estudio, 'Estudios');

            // Eliminar archivo temporal
           unlink($pdfFile->getRealPath());
        }

        return response()->json([
            'status'  => 'success',
            'mensaje' => 'Certificados generados y estudios registrados correctamente.'
        ]);
    }
}
