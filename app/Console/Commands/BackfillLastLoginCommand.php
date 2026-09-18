<?php

namespace App\Console\Commands;

use App\Services\UserLoginRecorder;
use Illuminate\Console\Command;

class BackfillLastLoginCommand extends Command
{
    protected $signature = 'coin:backfill-last-login';

    protected $description = 'Set last_login_at for users who registered but never logged in via the login form';

    public function handle(UserLoginRecorder $loginRecorder): int
    {
        $updated = $loginRecorder->backfillMissing();

        $this->components->info("Backfilled last_login_at for {$updated} user(s).");

        return self::SUCCESS;
    }
}
