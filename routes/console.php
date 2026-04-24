<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('deploy', function () {
    $this->call('migrate', ['--force' => true]);
    DB::table('sessions')->truncate();
    $this->info('Deploy complete: migrations ran and sessions flushed.');
})->purpose('Run migrations and flush all sessions (post-deploy)');
