<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VisitasSemanalesChart extends ChartWidget
{
    protected ?string $heading = 'Visitas Semanales';
    public ?string $filter = '7_dias';

    /**
     * 2. Declaramos las opciones que el usuario verá en el desplegable
     */
    protected function getFilters(): ?array
    {
        return [
            '7_dias' => 'Últimos 7 días',
            '30_dias' => 'Últimos 30 días',
            'este_mes' => 'Mes Actual',
            'este_anio' => 'Todo el Año',
        ];
    }


    protected function getData(): array
    {
        $filtroActivo = $this->filter;

        // Determinamos la fecha de inicio del reporte según el filtro
        switch ($filtroActivo) {
            case '30_dias':
                $inicio = Carbon::now()->subDays(29)->startOfDay();
                $formatoEjeX = 'D MMM'; // Ej: "28 May"
                $diasAtras = 29;
                break;

            case 'este_mes':
                $inicio = Carbon::now()->startOfMonth()->startOfDay();
                $formatoEjeX = 'D MMM';
                $diasAtras = Carbon::now()->day - 1;
                break;

            case 'este_anio':
                $inicio = Carbon::now()->startOfYear()->startOfDay();
                $formatoEjeX = 'MMMM'; // Agrupación mensual: "Mayo", "Junio"
                $diasAtras = null; // Usará lógica mensual especial abajo
                break;

            case '7_dias':
            default:
                $inicio = Carbon::now()->subDays(6)->startOfDay();
                $formatoEjeX = 'ddd D'; // Ej: "Jue 28"
                $diasAtras = 6;
                break;
        }

        $labelsEjeX = [];
        $conteosMap = [];

        if ($filtroActivo === 'este_anio') {

            for ($m = 1; $m <= 12; $m++) {
                $nombreMes = ucfirst(Carbon::create()->month($m)->isoFormat('MMMM'));
                $labelsEjeX[$m] = $nombreMes;
                $conteosMap[$m] = 0;
            }

            $visitasBD = DB::table('visitas')
                ->select(DB::raw('MONTH(created_at) as unidad_tiempo'), DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', $inicio)
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->get();

        } else {

          
            for ($i = $diasAtras; $i >= 0; $i--) {
                $fecha = Carbon::now()->subDays($i)->format('Y-m-d');
                $label = Carbon::parse($fecha)->isoFormat($formatoEjeX);

                $labelsEjeX[$fecha] = ucfirst($label);
                $conteosMap[$fecha] = 0;
            }

            // Consulta agrupada por DÍA limpio
            $visitasBD = DB::table('visitas')
                ->select(DB::raw('DATE(created_at) as unidad_tiempo'), DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', $inicio)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get();
        }

        foreach ($visitasBD as $registro) {
            if (array_key_exists($registro->unidad_tiempo, $conteosMap)) {
                $conteosMap[$registro->unidad_tiempo] = (int) $registro->total;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Visitas registradas',
                    'data' => array_values($conteosMap),
                    'backgroundColor' => 'rgba(255, 88, 0, 0.8)', 
                    'borderColor' => 'rgb(123, 35, 37)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => array_values($labelsEjeX),
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
