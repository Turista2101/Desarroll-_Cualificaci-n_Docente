<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Vida</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        .section {
            margin-top: 20px;
        }

        .section h2 {
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .documento {
            margin: 10px 0;
        }

        .documento img {
            max-width: 100%;
            max-height: 600px;
            display: block;
            margin: 10px 0;
        }

        .documento .file-link {
            color: blue;
            text-decoration: underline;
        }

        .documento .file-link:hover {
            cursor: pointer;
        }

        .documento img, .documento iframe {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>Hoja de Vida de {{ $usuario->name }} {{ $usuario->apellido ?? '' }}</h1>

    <p><strong>Email:</strong> {{ $usuario->email }}</p>
    <p><strong>Teléfono:</strong> {{ $usuario->informacionContactoUsuario->telefono_movil ?? 'No registrado' }}</p>

    {{-- Foto de perfil --}}
    <div class="section">
        <h2>Foto de Perfil</h2>
        @if ($usuario->fotoPerfilUsuario && $usuario->fotoPerfilUsuario->documentosFotoPerfil->count())
            @foreach ($usuario->fotoPerfilUsuario->documentosFotoPerfil as $doc)
                @php
                    $ruta = public_path('storage/' . $doc->archivo);
                    $mime = mime_content_type($ruta);
                    $imgBase64 = str_starts_with($mime, 'image/') ? base64_encode(file_get_contents($ruta)) : null;
                @endphp
                @if ($imgBase64)
                    <img src="data:{{ $mime }};base64,{{ $imgBase64 }}" alt="Foto de perfil">
                @else
                    <p>Documento no visible.</p>
                @endif
            @endforeach
        @else
            <p>No hay foto de perfil.</p>
        @endif
    </div>

    {{-- Estudios --}}
    <div class="section">
        <h2>Estudios</h2>
        @foreach ($usuario->estudiosUsuario as $e)
            <p><strong>{{ $e->titulo }}</strong> - {{ $e->institucion }}</p>
            @foreach ($e->documentosEstudio as $doc)
                @php
                    $ruta = public_path('storage/' . $doc->archivo);
                    $mime = mime_content_type($ruta);
                    $imgBase64 = str_starts_with($mime, 'image/') ? base64_encode(file_get_contents($ruta)) : null;
                @endphp
                @if ($imgBase64)
                    <img src="data:{{ $mime }};base64,{{ $imgBase64 }}" alt="Documento estudio">
                @else
                    <p>Documento no visible.</p>
                @endif
            @endforeach
        @endforeach
    </div>

    {{-- Experiencia --}}
    <div class="section">
        <h2>Experiencia</h2>
        @foreach ($usuario->experienciasUsuario as $e)
            <p><strong>{{ $e->cargo }}</strong> - {{ $e->empresa }}</p>
            @foreach ($e->documentosExperiencia as $doc)
                @php
                    $ruta = public_path('storage/' . $doc->archivo);
                    $mime = mime_content_type($ruta);
                    $imgBase64 = str_starts_with($mime, 'image/') ? base64_encode(file_get_contents($ruta)) : null;
                @endphp
                @if ($imgBase64)
                    <img src="data:{{ $mime }};base64,{{ $imgBase64 }}" alt="Documento experiencia">
                @else
                    <p>Documento no visible.</p>
                @endif
            @endforeach
        @endforeach
    </div>

    {{-- Otros documentos --}}
    @php
        $secciones = [
            'Idiomas' => $usuario->idiomasUsuario,
            'Producción Académica' => $usuario->produccionAcademicaUsuario,
            'RUT' => collect([$usuario->rutUsuario]),
            'EPS' => collect([$usuario->epsUsuario]),
        ];
        $documentosRelacion = [
            'Idiomas' => 'documentosIdioma',
            'Producción Académica' => 'documentosProduccionAcademica',
            'RUT' => 'documentosRut',
            'EPS' => 'documentosEps',
        ];
    @endphp

    @foreach ($secciones as $nombre => $coleccion)
        <div class="section">
            <h2>{{ $nombre }}</h2>
            @foreach ($coleccion as $item)
                @foreach ($item->{$documentosRelacion[$nombre]} ?? [] as $doc)
                    @php
                        $ruta = public_path('storage/' . $doc->archivo);
                        $mime = mime_content_type($ruta);
                        $imgBase64 = str_starts_with($mime, 'image/') ? base64_encode(file_get_contents($ruta)) : null;
                    @endphp
                    @if ($imgBase64)
                        <img src="data:{{ $mime }};base64,{{ $imgBase64 }}" alt="Documento {{ strtolower($nombre) }}">
                    @else
                        <p>Documento no visible.</p>
                    @endif
                @endforeach
            @endforeach
        </div>
    @endforeach

</body>
</html>