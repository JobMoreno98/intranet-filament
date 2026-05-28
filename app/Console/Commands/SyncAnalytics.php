<?php

namespace App\Console\Commands;

use App\Models\Recursos;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

#[Signature('analytics:sync')]
#[Description('Sincroniza las métricas de Redis a la Base de Datos relacional')]
class SyncAnalytics extends Command
{
    public function handle()
    {
        // Usamos la conexión limpia de Redis
        $redis = Redis::connection();

        $this->info('Iniciando vaciado de analíticas desde Redis (Mapeo bpej_)...');

        $visitas = [];

        $nombreCola = 'bpej_analytics:visitas_queue';

        $totalEnCola = $redis->llen($nombreCola);
        $this->line("Elementos encontrados en la cola de visitas: {$totalEnCola}");

        while ($rawVisita = $redis->lpop($nombreCola)) {
            $datos = json_decode($rawVisita, true);
            if ($datos) {
                $visitas[] = [
                    'ip' => $datos['ip'] ?? null,
                    'user_agent' => $datos['user_agent'] ?? null,
                    'url' => $datos['url'] ?? '/',
                    'user_id' => $datos['user_id'] ?? null,
                    'created_at' => $datos['created_at'] ?? now(),
                    'updated_at' => $datos['created_at'] ?? now(),
                ];
            }

            if (count($visitas) >= 100) {
                DB::table('visitas')->insert($visitas);
                $visitas = [];
            }
        }

        // Insertar los registros que hayan quedado en el último bloque
        if (!empty($visitas)) {
            DB::table('visitas')->insert($visitas);
        }


        // ---- 2. RECURSOS CONSULTADOS ----
        // ⚠️ Forzamos el nombre del Hash con su prefijo bpej_
        $nombreHash = 'bpej_analytics:recursos_vistas';

        $recursosVistas = $redis->hgetall($nombreHash);
        $this->line("Registros únicos de recursos a actualizar: " . count($recursosVistas));

        if (!empty($recursosVistas)) {
            foreach ($recursosVistas as $recursoId => $clicsAcumulados) {
                if ((int)$clicsAcumulados > 0) {

                    // Validamos que el recurso siga existiendo en la tabla antes de incrementar
                    $existe = DB::table('recursos')->where('id', $recursoId)->exists();

                    if ($existe) {
                        DB::table('recursos')
                            ->where('id', $recursoId)
                            ->increment('vistas_count', (int)$clicsAcumulados);
                    }
                }
            }
            // Eliminamos el hash de la RAM de un solo golpe
            $redis->del($nombreHash);
        }

        $this->info('¡Sincronización finalizada con éxito para la BPEJ!');
    }
}
