<?php

use Illuminate\Support\Facades\Schedule;

// Runs via `php artisan schedule:run` (cron) or `php artisan schedule:work` (long-running).
Schedule::command('expenses:weekly-summary')->weeklyOn(1, '06:00')->withoutOverlapping();

// Uncomment while practising so you don't wait a week to see it fire:
// Schedule::command('expenses:weekly-summary')->everyMinute();
