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

<x-mail::message>
# ¡Hola, {{ $user->name }}!

Con este formulario impreso (adjunto en PDF) y una identificación oficial puedes **SOLICITAR TU CREDENCIAL** para consultar de manera presencial los fondos de la Biblioteca Histórica. Sólo tienes que acudir con estos documentos al módulo de información ubicado en la **Planta Baja de la Biblioteca Contemporánea de la BPEJ** (Periférico Norte Manuel Gómez Morín no. 1695, Col. Belenes, C.P. 45150, Zapopan, Jalisco, México), de lunes a viernes de 9:00 a 16:00 horas.

## Copia de tus datos registrados

<x-mail::table>
| Campo | Respuesta |
| :--- | :--- |
| **Nombre(s)** | {{ $profile->nombres }} |
| **Apellido(s)** | {{ $profile->apellidos }} |
| **Nacionalidad** | {{ $profile->nacionalidad }} |
| **Fecha de nacimiento** | {{ $profile->fecha_nacimiento?->format('d/m/Y') }} |
| **Sexo** | {{ $sexos[$profile->sexo] ?? $profile->sexo }} |
| **Correo electrónico** | {{ $user->email }} |
| **Ciudad y país de residencia** | {{ $profile->ciudad_pais_residencia }} |
| **Grado de estudios** | {{ $grados[$profile->grado_estudios] ?? $profile->grado_estudios }} |
| **Institución de adscripción** | {{ $profile->institucion_adscripcion ?: 'No especificada' }} |
| **Motivo de consulta** | {{ $motivos[$profile->motivo_consulta] ?? $profile->motivo_consulta }} |
| **Tema de investigación** | {{ $profile->tema_investigacion ?: 'No especificado' }} |
</x-mail::table>

<x-mail::button :url="route('dashboard')" color="error">
Ir a la plataforma
</x-mail::button>

---

**AVISO DE PRIVACIDAD**

La Biblioteca Pública del Estado de Jalisco “Juan José Arreola” (en adelante UdeG), con domicilio en Periférico Norte No. 1695, Col. Belenes, CP 45100, en Zapopan, Jalisco, hace de su conocimiento que se considerará como información confidencial aquella que se encuentre contemplada en los artículos 3, fracciones IX y X de la LPDPPSOEJM; 21 de la LTAIPEJM; Lineamientos Cuadragésimo Octavo y Cuadragésimo Noveno de los Lineamientos de Clasificación; Lineamientos Décimo Sexto, Décimo Séptimo y Quincuagésimo Octavo de los Lineamientos de Protección, así como aquellos datos de una persona física identificada o identificable y la inherente a las personas jurídicas, los cuales podrán ser sometidos a tratamiento y serán única y exclusivamente utilizados para los fines que fueron proporcionados, de acuerdo con las finalidades y atribuciones establecidas en los artículos 1, 5 y 6 de la Ley Orgánica, así como 2 y 3 del Estatuto General, ambas legislaciones de la UdeG, de igual forma, para la prestación de los servicios que la misma ofrece conforme a las facultades y prerrogativas de la entidad universitaria correspondiente y estarán a resguardo y protección de la misma. Usted puede consultar nuestro Aviso de Privacidad integral en la siguiente página web: [http://www.transparencia.udg.mx/aviso-confidencialidad-integral](http://www.transparencia.udg.mx/aviso-confidencialidad-integral).

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>