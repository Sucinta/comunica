<?php

use App\Jobs\Sabium\FetchEstadosJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// TODO: avaliar mover FetchEstadosJob para fila 'sabium' se necessário no futuro
Schedule::job(new \App\Jobs\Sabium\FetchEstadosJob())
    ->everyMinute(); // ← Enviado para a fila "default" por padrão

Schedule::job(new \App\Jobs\Sabium\FetchCidadesJob())
    ->everyFiveMinutes(); // ← Enviado para a fila "default" por padrão


//app(Schedule::class)
//    ->job(FetchEstadosJob::class)
//    ->everyMinute();// ← Enviado para a fila "default" por padrão





