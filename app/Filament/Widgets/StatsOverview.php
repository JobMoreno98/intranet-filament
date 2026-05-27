<?php

namespace App\Filament\Widgets;

use App\Models\Recursos;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Conteo total de visitas a la página desde la tabla visitas
        $totalVisitas = DB::table('visitas')->count();

        // Sumatoria de todas las consultas de recursos cargados
        $totalConsultas = Recursos::sum('vistas_count');

        return [
            Stat::make('Visitas Totales a la Página', number_format($totalVisitas))
                ->description('Accesos globales acumulados')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Documentos Consultados', number_format($totalConsultas))
                ->description('Lecturas en el visor o descargas')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('danger'),
        ];
    }
}
