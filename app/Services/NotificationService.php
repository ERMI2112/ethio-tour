<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public function createForUser(?User $user, string $type, string $title, string $message): ?Notification
    {
        if (! $user) {
            return null;
        }

        try {
            return Notification::create([
                'user_id' => $user->user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'channel' => 'in_app',
                'sent_date' => now(),
                'read_status' => false,
            ]);
        } catch (Throwable $exception) {
            Log::warning('In-app notification could not be persisted.', ['type' => $type, 'user_id' => $user->user_id, 'error' => $exception->getMessage()]);

            return null;
        }
    }

    public function unreadCount(User $user): int
    {
        return $user->notifications()->where('read_status', false)->count();
    }
}
