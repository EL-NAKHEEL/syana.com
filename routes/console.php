<?php

use App\Jobs\GenerateSitemaps;
use Illuminate\Support\Facades\Schedule;

/*
| Needs one cron entry on the server:  * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
*/

// Sitemaps are also regenerated on every publish/update; this is the daily safety net.
Schedule::job(new GenerateSitemaps)->dailyAt('03:10');

// Backups (DB + uploaded media) to the off-server disk(s) in BACKUP_DISKS.
Schedule::command('backup:clean')->dailyAt('01:40');
Schedule::command('backup:run')->dailyAt('02:00')->onOneServer();
Schedule::command('backup:monitor')->dailyAt('06:00');

// Shared hosting has no supervisor: drain the database queue every minute.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping()
    ->when(fn () => config('queue.default') === 'database' && ! config('queue.supervised'));

Schedule::command('model:prune')->daily();
