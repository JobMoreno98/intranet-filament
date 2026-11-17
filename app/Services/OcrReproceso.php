<?php

namespace App\Services;

use App\Models\Recursos;
use App\Models\RecursosArchivos; // ajusta el namespace si tu modelo vive en otro lado
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Manda a la cola del worker Go (bpej_cola_ocr) el reproceso de OCR de los
 * recursos cuyo tipo_media es 'imagen'.
 *
 * Se encola una tarea por archivo (archivo_id), no por recurso, para que las
 * tareas sean chicas y no acaparen un lugar del worker mucho tiempo.
 */
class OcrReproceso
{
    /**
     * Llave SIN prefijo: igual que 'cola_procesamiento' en ProcesamientoGo, Laravel le
     * antepone el prefijo de Redis y en Redis queda como bpej_cola_ocr (lo que lee Go).
     */
    public const COLA = 'cola_ocr';

    /** Valor de recursos.tipo_media que permite el reproceso. */
    public const TIPO_PERMITIDO = 'imagen';

    /** Evita encolar el mismo archivo dos veces si se da doble clic. */
    private const MINUTOS_ANTIDUPLICADO = 15;

    /**
     * @param  array<int,int>  $recursoIds
     * @param  bool  $soloSinOcr  true = solo archivos cuyo OCR quedó vacío (por ejemplo, cuando falló Tesseract)
     * @return array{encolados:int, omitidos:int, recursos_no_imagen:int}
     */
    public static function encolar(array $recursoIds, string $langs = 'spa+eng', bool $soloSinOcr = false): array
    {
        $recursoIds = array_values(array_unique(array_filter($recursoIds)));

        if ($recursoIds === []) {
            return ['encolados' => 0, 'omitidos' => 0, 'recursos_no_imagen' => 0];
        }

        // Solo recursos de tipo imagen.
        $imagenIds = Recursos::query()
            ->whereIn('id', $recursoIds)
            ->where('tipo_media', self::TIPO_PERMITIDO)
            ->pluck('id')
            ->all();

        $noImagen = count($recursoIds) - count($imagenIds);

        // Solo archivos ya procesados (tienen main.webp para el OCR).
        $archivoIds = $imagenIds === [] ? [] : RecursosArchivos::query()
            ->whereIn('recursos_id', $imagenIds)
            ->where('status', 'listo')
            ->when($soloSinOcr, fn ($q) => $q->where(fn ($q) => $q->whereNull('ocr')->orWhere('ocr', '')))
            ->pluck('id')
            ->all();

        $payloads = [];
        $omitidos = 0;

        foreach ($archivoIds as $archivoId) {
            // Cache::add solo escribe si la llave no existe: así detectamos duplicados.
            if (! Cache::add("ocr-reproceso:{$archivoId}", 1, now()->addMinutes(self::MINUTOS_ANTIDUPLICADO))) {
                $omitidos++;
                continue;
            }

            $payloads[] = json_encode([
                'archivo_id' => $archivoId,
                'langs'      => $langs,
            ]);
        }

        foreach (array_chunk($payloads, 500) as $lote) {
            self::redis()->rpush(self::COLA, ...$lote);
        }

        return [
            'encolados'          => count($payloads),
            'omitidos'           => $omitidos,
            'recursos_no_imagen' => $noImagen,
        ];
    }

    /**
     * Misma conexión que usa ProcesamientoGo (Redis::lpush('cola_procesamiento')).
     */
    private static function redis()
    {
        return Redis::connection();
    }
}