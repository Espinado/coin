<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Collection;

class UserInAppNotificationService
{
    public function unreadCountForUser(int $userId): int
    {
        return UserNotification::query()
            ->where('user_id', $userId)
            ->unread()
            ->count();
    }

    /** @return Collection<int, UserNotification> */
    public function listForUser(User $user, int $limit = 50): Collection
    {
        return UserNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function markAsRead(UserNotification $notification, User $user): UserNotification
    {
        abort_unless((int) $notification->user_id === (int) $user->id, 403);

        $notification->markAsRead();

        return $notification->fresh();
    }

    public function findForUser(int $notificationId, User $user): ?UserNotification
    {
        return UserNotification::query()
            ->whereKey($notificationId)
            ->where('user_id', $user->id)
            ->first();
    }
}
