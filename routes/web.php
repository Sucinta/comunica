<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test', function () {

    $job = new \App\Jobs\Sabium\FetchDadosApoioJob();
    $job->handle(); // executa a lógica
    return 'Job executado manualmente';
});
