<?php

namespace App\Services;

use App\Events\UserNotificationCreated;
use App\Models\Admin;
use App\Models\PlatformBroadcast;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;

class PlatformBroadcastService
{
    public function __construct(
        private readonly UserInAppNotificationService $notifications,
    ) {}

    public function send(Admin $admin, string $title, string $body): PlatformBroadcast
    {
        return DB::transaction(function () use ($admin, $title, $body) {
            $broadcast = PlatformBroadcast::query()->create([
                'admin_id' => $admin->id,
                'title' => $title,
                'body' => $body,
                'recipients_count' => 0,
            ]);

            $recipientCount = 0;
            $now = now();

            User::query()
                ->where('is_blocked', false)
                ->orderBy('id')
                ->select('id')
                ->chunkById(200, function ($users) use ($broadcast, $title, $body, $now, &$recipientCount) {
                    $rows = $users->map(fn (User $user) => [
                        'user_id' => $user->id,
                        'platform_broadcast_id' => $broadcast->id,
                        'title' => $title,
                        'body' => $body,
                        'read_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    UserNotification::query()->insert($rows);
                    $recipientCount += count($rows);

                    $notifications = UserNotification::query()
                        ->where('platform_broadcast_id', $broadcast->id)
                        ->whereIn('user_id', $users->pluck('id'))
                        ->get();

                    foreach ($notifications as $notification) {
                        UserNotificationCreated::dispatch($notification);
                    }
                });

            $broadcast->update(['recipients_count' => $recipientCount]);

            return $broadcast->fresh(['admin']);
        });
    }
}
