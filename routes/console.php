<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Recursos;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:clean-chunks')->dailyAt('01:00')->withoutOverlapping();

Schedule::call(function () {
    Recursos::query()
        ->where('created_at', '>=', now()->subDay())
        ->with(['acervo', 'coleccion', 'archivos'])
        ->searchable(500);
})
    ->hourly()
    ->name('reindexar-recursos-recientes')
    ->withoutOverlapping();
