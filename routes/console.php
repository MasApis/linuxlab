<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal pembersihan sesi terminal kedaluwarsa secara otomatis (FR-TERM-04)
Schedule::command('terminal:clean-expired')->everyMinute();

