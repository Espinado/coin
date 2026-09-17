<?php

namespace App\Events;

use App\Models\UserNotification;
use App\Services\UserInAppNotificationService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public UserNotification $notification,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notifications.user.'.$this->notification->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserNotificationCreated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $notification = $this->notification;

        return [
            'notification' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'created_at' => $notification->created_at?->format('d.m.Y H:i'),
                'is_read' => $notification->isRead(),
            ],
            'total_unread' => app(UserInAppNotificationService::class)
                ->unreadCountForUser((int) $notification->user_id),
            'user_toast' => $notification->title,
        ];
    }
}
