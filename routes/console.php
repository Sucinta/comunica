<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
 * TODO: avaliar mover FetchEstadosJob para fila 'sabium' se necessário no futuro
 */





/*
 * Jobs Estacionados | Temporário
 */
//Schedule::job(new \App\Jobs\Sabium\FetchEstadosJob())
//    ->everyMinute(); // ← Enviado para a fila "default" por padrão
//
//Schedule::job(new \App\Jobs\Sabium\FetchCidadesJob())
//    ->everyFiveMinutes(); // ← Enviado para a fila "default" por padrão
