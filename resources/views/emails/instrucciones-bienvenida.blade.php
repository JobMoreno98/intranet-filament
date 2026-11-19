<x-mail::message>
    # ¡Hola, {{ $user->name }}!

    Con este formulario impreso y una identificación oficial puedes SOLICITAR TU CREDENCIAL para consultar de manera
    presencial los fondos de la Biblioteca Histórica. Sólo tienes que acudir con estos documentos al módulo de
    información ubicado en la Planta Baja de la Biblioteca Contemporánea de la BPEJ (Periférico Norte Manuel Gómez Morín
    no. 1695, Col. Belenes, C.P. 45150, Zapopan, Jalisco, México), de lunes a viernes de 9 a 16 horas.



    <hr>
    <b> AVISO DE PRIVACIDAD</b>

    La Biblioteca Pública del Estado de Jalisco “Juan José Arreola” (en adelante UdeG), con domicilio en Periférico
    Norte No. 1695, Col. Belenes, CP 45100, en Zapopan, Jalisco, hace de su conocimiento que se considerará como
    información confidencial aquella que se encuentre contemplada en los artículos 3, fracciones IX y X de la
    LPDPPSOEJM; 21 de la LTAIPEJM; Lineamientos Cuadragésimo Octavo y Cuadragésimo Noveno de los Lineamientos de
    Clasificación; Lineamientos Décimo Sexto, Décimo Séptimo y Quincuagésimo Octavo de los Lineamientos de Protección,
    así como aquellos datos de una persona física identificada o identificable y la inherente a las personas jurídicas,
    los cuales podrán ser sometidos a tratamiento y serán única y exclusivamente utilizados para los fines que fueron
    proporcionados, de acuerdo con las finalidades y atribuciones establecidas en los artículos 1, 5 y 6 de la Ley
    Orgánica, así como 2 y 3 del Estatuto General, ambas legislaciones de la UdeG, de igual forma, para la prestación de
    los servicios que la misma ofrece conforme a las facultades y prerrogativas de la entidad universitaria
    correspondiente y estarán a resguardo y protección de la misma. Usted puede consultar nuestro Aviso de Privacidad
    integral en la siguiente página web: http://www.transparencia.udg.mx/aviso-confidencialidad-integral

    <x-mail::button :url="route('dashboard')" color="error">
        Ir a la plataforma
    </x-mail::button>

    Saludos,<br>
    {{ config('app.name') }}
</x-mail::message>
