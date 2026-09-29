<?php

use App\Console\Commands\FinalizarEventosPassados;
use App\Console\Commands\ProcessLembretes;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(FinalizarEventosPassados::class)->monthlyOn(1, '00:00');
Schedule::command(ProcessLembretes::class)->everyMinute()->withoutOverlapping();
