<?php

use Illuminate\Support\Facades\Schedule;

// Check for missed attendance every 5 minutes during working hours
Schedule::command('attendance:check-missed')
    ->everyFiveMinutes()
    ->weekdays()
    ->between('7:00', '21:00');

// Sync external data daily at 5 AM
Schedule::command('attendance:sync')
    ->dailyAt('05:00');
