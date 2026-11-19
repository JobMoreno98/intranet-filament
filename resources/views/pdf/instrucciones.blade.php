@php
    $profile = $user->profile;

    $sexos = [
        'hombre' => 'Hombre',
        'mujer' => 'Mujer',
        'prefiero_no_decirlo' => 'Prefiero no decirlo',
    ];

    $grados = [
        'sin_estudios' => 'Sin estudios',
        'educacion_basica' => 'Educación básica',
        'bachillerato' => 'Bachillerato',
        'licenciatura' => 'Licenciatura',
        'posgrado' => 'Posgrado',
    ];

    $motivos = [
        'personal' => 'Personal',
        'escolar_tesis' => 'Escolar o tesis',
        'investigacion_academica_profesional' => 'Investigación académica o profesional',
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 1.5cm 1.8cm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #1f2937;
            line-height: 1.45;
        }
        .header {
            border-bottom: 2.5px solid #7c2422;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header-title {
            color: #7c2422;
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 3px 0;
        }
        .header-subtitle {
            font-size: 11px;
            color: #4b5563;
            margin: 0;
        }
        .instructions {
            background-color: #fdf8f8;
            border-left: 4px solid #7c2422;
            padding: 10px 12px;
            margin-bottom: 16px;
            font-size: 10px;
            text-align: justify;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #7c2422;
            margin: 0 0 6px 0;
            letter-spacing: 0.4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 9px;
            text-align: left;
            vertical-align: top;
        }
        table.data-table th {
            width: 36%;
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        .signature-box {
            margin-top: 24px;
            margin-bottom: 18px;
            text-align: center;
        }
        .signature-line {
            width: 240px;
            border-top: 1px solid #374151;
            margin: 0 auto 4px auto;
        }
        .signature-label {
            font-size: 9.5px;
            color: #4b5563;
        }
        .privacy {
            border-top: 1px solid #9ca3af;
            padding-top: 8px;
            font-size: 7.5px;
            color: #4b5563;
            text-align: justify;
            line-height: 1.35;
        }
        .privacy-title {
            font-weight: bold;
            color: #1f2937;
            display: block;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="header-title">Solicitud de Credencial — Biblioteca Histórica</p>
        <p class="header-subtitle">Biblioteca Pública del Estado de Jalisco “Juan José Arreola” | Fecha de registro: {{ $user->created_at?->format('d/m/Y') }}</p>
    </div>

    <div class="instructions">
        <strong>¡Hola, {{ $user->name }}!</strong><br>
        Con este formulario impreso y una identificación oficial puedes <strong>SOLICITAR TU CREDENCIAL</strong> para consultar de manera presencial los fondos de la Biblioteca Histórica. Sólo tienes que acudir con estos documentos al módulo de información ubicado en la <strong>Planta Baja de la Biblioteca Contemporánea de la BPEJ</strong> (Periférico Norte Manuel Gómez Morín no. 1695, Col. Belenes, C.P. 45150, Zapopan, Jalisco, México), de lunes a viernes de 9:00 a 16:00 horas.
    </div>

    <p class="section-title">Datos del solicitante</p>
    <table class="data-table">
        <tr>
            <th>1. Apellido(s)</th>
            <td>{{ $profile->apellidos }}</td>
        </tr>
        <tr>
            <th>2. Nombre(s)</th>
            <td>{{ $profile->nombres }}</td>
        </tr>
        <tr>
            <th>3. Nacionalidad</th>
            <td>{{ $profile->nacionalidad }}</td>
        </tr>
        <tr>
            <th>4. Fecha de nacimiento</th>
            <td>{{ $profile->fecha_nacimiento?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>5. Sexo</th>
            <td>{{ $sexos[$profile->sexo] ?? $profile->sexo }}</td>
        </tr>
        <tr>
            <th>6. Correo electrónico</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th>7. Ciudad y país de residencia</th>
            <td>{{ $profile->ciudad_pais_residencia }}</td>
        </tr>
        <tr>
            <th>8. Grado de estudios</th>
            <td>{{ $grados[$profile->grado_estudios] ?? $profile->grado_estudios }}</td>
        </tr>
        <tr>
            <th>9. Institución de adscripción</th>
            <td>{{ $profile->institucion_adscripcion ?: 'No especificada' }}</td>
        </tr>
        <tr>
            <th>10. Motivo de consulta</th>
            <td>{{ $motivos[$profile->motivo_consulta] ?? $profile->motivo_consulta }}</td>
        </tr>
        <tr>
            <th>11. Tema de investigación</th>
            <td>{{ $profile->tema_investigacion ?: 'No especificado' }}</td>
        </tr>
    </table>

    <div class="signature-box">
        <div class="signature-line"></div>
        <span class="signature-label">Firma del solicitante</span>
    </div>

    <div class="privacy">
        <span class="privacy-title">AVISO DE PRIVACIDAD</span>
        La Biblioteca Pública del Estado de Jalisco “Juan José Arreola” (en adelante UdeG), con domicilio en Periférico Norte No. 1695, Col. Belenes, CP 45100, en Zapopan, Jalisco, hace de su conocimiento que se considerará como información confidencial aquella que se encuentre contemplada en los artículos 3, fracciones IX y X de la LPDPPSOEJM; 21 de la LTAIPEJM; Lineamientos Cuadragésimo Octavo y Cuadragésimo Noveno de los Lineamientos de Clasificación; Lineamientos Décimo Sexto, Décimo Séptimo y Quincuagésimo Octavo de los Lineamientos de Protección, así como aquellos datos de una persona física identificada o identificable y la inherente a las personas jurídicas, los cuales podrán ser sometidos a tratamiento y serán única y exclusivamente utilizados para los fines que fueron proporcionados, de acuerdo con las finalidades y atribuciones establecidas en los artículos 1, 5 y 6 de la Ley Orgánica, así como 2 y 3 del Estatuto General, ambas legislaciones de la UdeG, de igual forma, para la prestación de los servicios que la misma ofrece conforme a las facultades y prerrogativas de la entidad universitaria correspondiente y estarán a resguardo y protección de la misma. Usted puede consultar nuestro Aviso de Privacidad integral en la siguiente página web: http://www.transparencia.udg.mx/aviso-confidencialidad-integral
    </div>
</body>
</html>