<?php

namespace App\Services;

use App\Models\Usuario\User;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ConvertidorPDFService;

class GeneradorHojaDeVidaPDFService
{
    protected $convertidorPDFService;

    public function __construct(ConvertidorPDFService $convertidorPDFService)
    {
        $this->convertidorPDFService = $convertidorPDFService;
    }

    public function generar($idUsuario)
    {
        try {
            $usuario = User::with([
                'documentosUser',
                'fotoPerfilUsuario.documentosFotoPerfil',
                'informacionContactoUsuario.documentosInformacionContacto',
                'estudiosUsuario.documentosEstudio',
                'experienciasUsuario.documentosExperiencia',
                'epsUsuario.documentosEps',
                'idiomasUsuario.documentosIdioma',
                'rutUsuario.documentosRut',
                'produccionAcademicaUsuario.documentosProduccionAcademica',
                'aptitudesUsuario',
            ])->findOrFail($idUsuario);

            $pdf = new Fpdi();
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', '', 12);

            // --- Foto de perfil ---
            $this->imprimirFotoPerfil($pdf, $usuario);

            $pdf->Ln(70);

            // --- Información personal ---
            $this->imprimirInformacionPersonal($pdf, $usuario);

            // --- Aptitudes ---
            $this->imprimirAptitudes($pdf, $usuario->aptitudesUsuario);

            // --- Documento de identificación ---
            $this->procesarRelacionMultiple($pdf, $usuario->documentosUser, 'documentosUser', 'Documento de Identificación');

            // --- EPS ---
            $this->imprimirEPS($pdf, $usuario->epsUsuario);

            // --- Información de contacto ---
            $this->procesarRelacionSimple($pdf, $usuario->informacionContactoUsuario, 'documentosInformacionContacto', 'Información de Contacto');

            // --- RUT ---
            $this->procesarRelacionSimple($pdf, $usuario->rutUsuario, 'documentosRut', 'RUT');

            // --- Idiomas ---
            $this->imprimirIdiomas($pdf, $usuario->idiomasUsuario);

            // --- Estudios ---
            $this->imprimirEstudios($pdf, $usuario->estudiosUsuario);

            // --- Experiencias ---
            $this->imprimirExperiencias($pdf, $usuario->experienciasUsuario);

            // --- Producción Académica ---
            $this->imprimirProduccionAcademica($pdf, $usuario->produccionAcademicaUsuario);

            $pdf->Output('hoja_de_vida_' . $idUsuario . '.pdf', 'I');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al generar el PDF.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------
    // Métodos auxiliares
    // ------------------------

    private function imprimirLineaCentrada($pdf, $etiqueta, $valor)
    {
        $textoCompleto = $etiqueta . ' ' . $valor;
        $anchoTexto = $pdf->GetStringWidth($textoCompleto);
        $paginaAncho = $pdf->GetPageWidth();
        $x = ($paginaAncho - $anchoTexto) / 2;
        $pdf->SetX($x);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Write(10, $etiqueta . ' ');
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->Write(10, $valor);
        $pdf->Ln(12);
    }

    private function imprimirFotoPerfil($pdf, $usuario)
    {
        if ($usuario->fotoPerfilUsuario && $usuario->fotoPerfilUsuario->documentosFotoPerfil) {
            $fotoRelativePath = $usuario->fotoPerfilUsuario->documentosFotoPerfil->first()->archivo ?? null;
            if ($fotoRelativePath) {
                $fotoStoragePath = public_path('storage/' . $fotoRelativePath);
                if (file_exists($fotoStoragePath)) {
                    $pageWidth = $pdf->GetPageWidth();
                    $imageWidth = 50;
                    $x = ($pageWidth - $imageWidth) / 2;
                    $pdf->Image($fotoStoragePath, $x, 20, $imageWidth, 50, 'JPG');
                }
            }
        }
    }

    private function imprimirInformacionPersonal($pdf, $usuario)
    {
        $this->imprimirLineaCentrada($pdf, 'Nombre:', $usuario->primer_nombre . ' ' . $usuario->segundo_nombre);
        $this->imprimirLineaCentrada($pdf, 'Apellido:', $usuario->primer_apellido . ' ' . $usuario->segundo_apellido);
        $this->imprimirLineaCentrada($pdf, 'Tipo de identificación:', $usuario->tipo_identificacion);
        $this->imprimirLineaCentrada($pdf, 'Número de identificación:', $usuario->numero_identificacion);
        $this->imprimirLineaCentrada($pdf, 'Género:', $usuario->genero);
        $this->imprimirLineaCentrada($pdf, 'Correo:', $usuario->email);
        $this->imprimirLineaCentrada($pdf, 'Teléfono:', $usuario->telefono);
    }

    private function imprimirAptitudes($pdf, $aptitudes)
    {
        if ($aptitudes && $aptitudes->count() > 0) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Aptitudes', 0, 1, 'C');
            $pdf->SetFont('Helvetica', '', 12);
            foreach ($aptitudes as $aptitud) {
                $pdf->Cell(0, 10, '- ' . $aptitud->nombre_aptitud, 0, 1);
            }
        }
    }

    private function imprimirEPS($pdf, $epsUsuario)
    {
        if ($epsUsuario) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'EPS', 0, 1, 'C');
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->Cell(0, 10, 'Nombre EPS: ' . $epsUsuario->nombre_eps, 0, 1);
            $pdf->Cell(0, 10, 'Tipo Afiliación: ' . $epsUsuario->tipo_afiliacion, 0, 1);
            $pdf->Cell(0, 10, 'Estado Afiliación: ' . $epsUsuario->estado_afiliacion, 0, 1);
            $pdf->Cell(0, 10, 'Fecha Afiliación Efectiva: ' . $epsUsuario->fecha_afiliacion_efectiva, 0, 1);
            $pdf->Cell(0, 10, 'Fecha Finalización Afiliación: ' . ($epsUsuario->fecha_finalizacion_afiliacion ?? 'No aplica'), 0, 1);
            $pdf->Cell(0, 10, 'Tipo Afiliado: ' . $epsUsuario->tipo_afiliado, 0, 1);
            $pdf->Cell(0, 10, 'Número Afiliado: ' . ($epsUsuario->numero_afiliado ?? 'No aplica'), 0, 1);
            $this->procesarRelacionSimple($pdf, $epsUsuario, 'documentosEps', 'EPS');
        }
    }

    private function imprimirIdiomas($pdf, $idiomas)
    {
        if ($idiomas && $idiomas->count() > 0) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Idiomas', 0, 1, 'C');
            $pdf->SetFont('Helvetica', '', 12);
            foreach ($idiomas as $idioma) {
                $pdf->Cell(0, 10, 'Idioma: ' . $idioma->idioma, 0, 1);
                $pdf->Cell(0, 10, 'Institución: ' . $idioma->institucion_idioma, 0, 1);
                $pdf->Cell(0, 10, 'Fecha Certificado: ' . $idioma->fecha_certificado, 0, 1);
                $pdf->Cell(0, 10, 'Nivel: ' . $idioma->nivel, 0, 1);
                $pdf->Ln(5);
                $this->procesarDocumentoRelacion($idioma->documentosIdioma, $pdf, 'Idioma');
            }
        }
    }

    private function imprimirEstudios($pdf, $estudios)
    {
        if ($estudios && $estudios->count() > 0) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Estudios', 0, 1, 'C');
            $pdf->SetFont('Helvetica', '', 12);
            foreach ($estudios as $estudio) {
                $pdf->Cell(0, 10, 'Tipo Estudio: ' . $estudio->tipo_estudio, 0, 1);
                $pdf->Cell(0, 10, 'Institución: ' . $estudio->institucion, 0, 1);
                $pdf->Cell(0, 10, 'Título: ' . ($estudio->titulo_estudio ?? 'No aplica'), 0, 1);
                $pdf->Cell(0, 10, 'Graduado: ' . $estudio->graduado, 0, 1);
                $pdf->Cell(0, 10, 'Fecha Inicio: ' . $estudio->fecha_inicio, 0, 1);
                $pdf->Cell(0, 10, 'Fecha Fin: ' . ($estudio->fecha_fin ?? 'No aplica'), 0, 1);
                $pdf->Ln(5);
                $this->procesarDocumentoRelacion($estudio->documentosEstudio, $pdf, 'Estudio');
            }
        }
    }

    private function imprimirExperiencias($pdf, $experiencias)
    {
        if ($experiencias && $experiencias->count() > 0) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Experiencia Laboral', 0, 1, 'C');
            $pdf->SetFont('Helvetica', '', 12);
            foreach ($experiencias as $experiencia) {
                $pdf->Cell(0, 10, 'Institución: ' . $experiencia->institucion_experiencia, 0, 1);
                $pdf->Cell(0, 10, 'Cargo: ' . $experiencia->cargo, 0, 1);
                $pdf->Cell(0, 10, 'Fecha Inicio: ' . $experiencia->fecha_inicio, 0, 1);
                $pdf->Cell(0, 10, 'Fecha Fin: ' . ($experiencia->fecha_finalizacion ?? 'Actualmente'), 0, 1);
                $pdf->Ln(5);
                $this->procesarDocumentoRelacion($experiencia->documentosExperiencia, $pdf, 'Experiencia');
            }
        }
    }

    private function imprimirProduccionAcademica($pdf, $producciones)
    {
        if ($producciones && $producciones->count() > 0) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Producción Académica', 0, 1, 'C');
            $pdf->SetFont('Helvetica', '', 12);
            foreach ($producciones as $produccion) {
                $pdf->Cell(0, 10, 'Título: ' . $produccion->titulo, 0, 1);
                $pdf->Cell(0, 10, 'Fecha Divulgación: ' . $produccion->fecha_divulgacion, 0, 1);
                $pdf->Ln(5);
                $this->procesarDocumentoRelacion($produccion->documentosProduccionAcademica, $pdf, 'Producción Académica');
            }
        }
    }

    private function procesarRelacionSimple($pdf, $modelo, $campoDocumentos, $titulo)
    {
        if ($modelo && $modelo->$campoDocumentos) {
            foreach ($modelo->$campoDocumentos as $documento) {
                $this->procesarDocumento($documento, $pdf, $titulo);
            }
        }
    }

    private function procesarRelacionMultiple($pdf, $modelos, $campoDocumentos, $titulo)
    {
        if ($modelos) {
            foreach ($modelos as $modelo) {
                if ($modelo->$campoDocumentos) {
                    foreach ($modelo->$campoDocumentos as $documento) {
                        $this->procesarDocumento($documento, $pdf, $titulo);
                    }
                }
            }
        }
    }

    private function procesarDocumentoRelacion($documentos, $pdf, $tipoDocumento)
    {
        if ($documentos) {
            foreach ($documentos as $documento) {
                $this->procesarDocumento($documento, $pdf, $tipoDocumento);
            }
        }
    }

    private function procesarDocumento($documento, $pdf, $tipoDocumento)
    {
        $pdfPath = public_path('storage/' . $documento->archivo);

        if (file_exists($pdfPath)) {
            try {
                $pdfConvertido = $this->convertidorPDFService->convertir($pdfPath);
                $pageCount = $pdf->setSourceFile($pdfConvertido);

                for ($page = 1; $page <= $pageCount; $page++) {
                    $tplIdx = $pdf->importPage($page);
                    $pdf->AddPage();
                    $pdf->useTemplate($tplIdx, 10, 10, 190);
                }


                if (realpath($pdfConvertido) !== realpath($pdfPath)) {
                    @unlink($pdfConvertido);
                }
            } catch (\Exception $e) {
                $pdf->AddPage();
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->MultiCell(0, 10, 'Error al procesar ' . $tipoDocumento . ': ' . $e->getMessage(), 0, 'L');
                Log::error('Error al procesar ' . $tipoDocumento . ': ' . $e->getMessage());
            }
        } else {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->Cell(0, 10, 'Documento no encontrado: ' . $pdfPath, 0, 1);
        }
    }
}
