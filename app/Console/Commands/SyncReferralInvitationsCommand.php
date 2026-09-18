<?php

namespace App\Console\Commands;

use App\Services\ReferralService;
use Illuminate\Console\Command;

class SyncReferralInvitationsCommand extends Command
{
    protected $signature = 'coin:sync-referral-invitations';

    protected $description = 'Backfill referral invitation rows and reconcile invited_count counters';

    public function handle(ReferralService $referrals): int
    {
        $synced = $referrals->syncInvitationRecords();

        $this->components->info("Synced {$synced} referral invitation record(s).");

        return self::SUCCESS;
    }
}
