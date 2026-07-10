<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Penjadwalan Akuisisi Data
|--------------------------------------------------------------------------
| Menarik data sensor & menghitung status setiap menit, lalu mengirim
| notifikasi email bila status berubah menjadi WARNING/DANGER.
| Berjalan tanpa perlu membuka dashboard.
*/
Schedule::command('sensors:sync')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();