<?php

namespace App\Services;

use App\Models\Recursos;
use App\Models\RecursosArchivos; // ajusta el namespace si tu modelo vive en otro lado
use Illuminate\Support\Facades\Cache;

/**
 * Vuelve a mandar al worker Go los archivos que quedaron con status 'error' y,
 * en recursos de tipo imagen, los que llevan mucho tiempo en 'en_cola'
 * (el worker solo marca 'error' en video/audio, así que una imagen que falla
 * se queda en 'en_cola' para siempre).
 * Usa el mismo payload que la subida original (ProcesamientoGo::enviar) y el
 * original, que nunca se borra de storage/app/private.
 */
class ReprocesoArchivos
{
    /**
     * PDF queda fuera a propósito: el worker inserta una fila nueva por cada
     * página 2..N, así que reprocesar un PDF que falló a la mitad duplicaría
     * páginas. Imagen, video y audio sí son seguros (se sobrescriben por archivo_id).
     */
    public const TIPOS_PERMITIDOS = ['imagen', 'video', 'audio'];

    private const MINUTOS_ANTIDUPLICADO = 15;

    /**
     * Una imagen en 'en_cola' sin cambios por más de este tiempo se considera atascada.
     * Súbelo si a veces tienes colas largas (subidas masivas): si la imagen seguía
     * esperando su turno en Redis, se procesará dos veces (no pasa nada grave, el
     * procesamiento de imágenes se sobrescribe, pero gasta CPU).
     */
    public const MINUTOS_ATASCADA = 120;

    /**
     * @param  array<int,int>  $recursoIds
     * @return array{encolados:int, atascadas:int, sin_original:int, duplicados:int, recursos_omitidos:int}
     */
    public static function reprocesarErrores(array $recursoIds, bool $incluirAtascadas = true): array
    {
        $recursoIds = array_values(array_unique(array_filter($recursoIds)));

        $recursos = Recursos::query()
            ->with('coleccion')
            ->whereIn('id', $recursoIds)
            ->get();

        $permitidos = $recursos->whereIn('tipo_media', self::TIPOS_PERMITIDOS);

        $r = [
            'encolados'         => 0,
            'atascadas'         => 0,
            'sin_original'      => 0,
            'duplicados'        => 0,
            'recursos_omitidos' => $recursos->count() - $permitidos->count(),
        ];

        foreach ($permitidos as $recurso) {
            $archivos = RecursosArchivos::query()
                ->where('recursos_id', $recurso->id)
                ->where(function ($q) use ($recurso, $incluirAtascadas) {
                    $q->where('status', 'error');

                    // Solo imágenes: un video puede tardar horas legítimamente.
                    if ($incluirAtascadas && $recurso->tipo_media === 'imagen') {
                        $q->orWhere(fn ($q) => $q
                            ->where('status', 'en_cola')
                            ->where('updated_at', '<', now()->subMinutes(self::MINUTOS_ATASCADA)));
                    }
                })
                ->get();

            foreach ($archivos as $archivo) {
                if (! is_file(storage_path('app/private/' . $archivo->path_original))) {
                    $r['sin_original']++;
                    continue;
                }

                if (! Cache::add("reproceso-error:{$archivo->id}", 1, now()->addMinutes(self::MINUTOS_ANTIDUPLICADO))) {
                    $r['duplicados']++;
                    continue;
                }

                if ($archivo->status === 'en_cola') {
                    $r['atascadas']++;
                }

                // Se renueva updated_at para que no se vuelva a tomar como atascada enseguida.
                // forceFill con updated_at: si el status ya era 'en_cola', update() no cambiaría nada.
                $archivo->forceFill(['status' => 'en_cola', 'updated_at' => now()])->save();

                // Al final de la cola: no se adelanta a las subidas nuevas.
                ProcesamientoGo::enviar($archivo, $recurso, 'update', alFinal: true);

                $r['encolados']++;
            }
        }

        return $r;
    }
}