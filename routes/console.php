<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Flux Google Merchant Center : régénéré chaque nuit + relançable à la main
// (`php artisan feed:build`). À déclencher aussi après un import catalogue.
Schedule::command('feed:build')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->runInBackground();
