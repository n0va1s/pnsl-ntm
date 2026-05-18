<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('mov:deletar-eventos-finalizados')
    ->dailyAt('23:55')
    ->onFailure(function () {
        Log::error('Scheduler: mov:deletar-eventos-finalizados falhou.');
    });

Schedule::command('mov:enviar-emails-aniversario')
    ->dailyAt('08:00')
    ->runInBackground()
    ->onFailure(function () {
        Log::error('Scheduler: mov:enviar-emails-aniversario falhou.');
    });
