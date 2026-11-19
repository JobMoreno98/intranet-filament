<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #27272a; line-height: 1.6; }
        .header { border-bottom: 2px solid #7c2422; padding-bottom: 10px; margin-bottom: 20px; }
        h1 { color: #7c2422; font-size: 20px; margin: 0; }
        .box { background: #f4f4f5; padding: 15px; border-radius: 6px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Instrucciones de Acceso y Consulta</h1>
        <p>Bienvenido(a), <strong>{{ $user->name }}</strong></p>
    </div>

    <p>Tu cuenta ha sido verificada exitosamente. A continuación te compartimos las instrucciones para utilizar la plataforma:</p>

    <div class="box">
        <ol>
            <li><strong>Acceso al catálogo:</strong> Inicia sesión con tu correo electrónico registrado (<code>{{ $user->email }}</code>).</li>
            <li><strong>Búsqueda de acervos:</strong> Utiliza los filtros por colección, fecha o palabra clave.</li>
            <li><strong>Normas de uso:</strong> Cita adecuadamente la fuente documental en tus trabajos académicos o de investigación.</li>
        </ol>
    </div>
</body>
</html>