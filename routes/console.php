<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auditoría automatizada de métricas de redes sociales cada 24 horas
Schedule::command('app:auditar-perfiles-sociales')
    ->dailyAt('04:00')
    ->runInBackground()
    ->withoutOverlapping();
